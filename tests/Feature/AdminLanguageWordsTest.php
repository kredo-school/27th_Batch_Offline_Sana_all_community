<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LibreTranslateService;
use App\Support\LanguageFileWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminLanguageWordsTest extends TestCase
{
    use RefreshDatabase;

    private string $languageDirectory;
    private int $libreTranslateStatus = 200;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google_translate.enabled' => false]);
        config(['app.supported_locales' => [
            'en' => 'English',
            'ja' => '日本語',
            'es' => 'Español',
            'ceb' => 'Cebuano',
        ]]);
        $this->languageDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'admin-languages-'.uniqid();
        mkdir($this->languageDirectory);
        file_put_contents(
            $this->languageDirectory.DIRECTORY_SEPARATOR.'ja.json',
            json_encode(['Hello' => 'こんにちは'], JSON_UNESCAPED_UNICODE)."\n",
        );
        Http::fake(function ($request) {
            if ($this->libreTranslateStatus >= 400) {
                return Http::response('error', $this->libreTranslateStatus);
            }

            $translated = match ($request['target']) {
                'ja' => 'さようなら',
                'es' => 'Adiós',
                default => 'Goodbye',
            };

            return Http::response(['translatedText' => $translated]);
        });
        $this->app->instance(LanguageFileWriter::class, new LanguageFileWriter($this->languageDirectory));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->languageDirectory.DIRECTORY_SEPARATOR.'*') as $file) {
            unlink($file);
        }
        rmdir($this->languageDirectory);

        parent::tearDown();
    }

    public function test_libretranslate_is_used_before_google_fallback(): void
    {
        config([
            'services.google_translate.enabled' => true,
            'services.libretranslate.url' => 'https://libretranslate.test',
        ]);

        $service = new class extends LibreTranslateService
        {
            public bool $googleCalled = false;

            public function translateWithGoogleFallback(
                string $text,
                string $targetLang,
                string $sourceLang = 'en',
            ): ?string {
                $this->googleCalled = true;

                return 'Google translation';
            }
        };

        $this->assertSame('Adiós', $service->translate('Hello', 'es'));
        $this->assertFalse($service->googleCalled);
        Http::assertSent(fn ($request) => $request->url() === 'https://libretranslate.test/translate');
    }

    public function test_google_is_used_when_libretranslate_is_unavailable(): void
    {
        config([
            'services.google_translate.enabled' => true,
            'services.libretranslate.url' => null,
        ]);

        $service = new class extends LibreTranslateService
        {
            public bool $googleCalled = false;

            public function translateWithGoogleFallback(
                string $text,
                string $targetLang,
                string $sourceLang = 'en',
            ): ?string {
                $this->googleCalled = true;

                return 'Hola';
            }
        };

        $this->assertSame('Hola', $service->translate('Hello', 'es'));
        $this->assertTrue($service->googleCalled);
        Http::assertNothingSent();
    }

    public function test_google_is_used_when_libretranslate_fails(): void
    {
        $this->libreTranslateStatus = 500;
        config([
            'services.google_translate.enabled' => true,
            'services.libretranslate.url' => 'https://libretranslate.test',
        ]);

        $service = new class extends LibreTranslateService
        {
            public bool $googleCalled = false;

            public function translateWithGoogleFallback(
                string $text,
                string $targetLang,
                string $sourceLang = 'en',
            ): ?string {
                $this->googleCalled = true;

                return 'Hola';
            }
        };

        $this->assertSame('Hola', $service->translate('Hello', 'es'));
        $this->assertTrue($service->googleCalled);
        Http::assertSent(fn ($request) => $request->url() === 'https://libretranslate.test/translate');
    }

    public function test_admin_can_create_read_update_and_delete_language_words(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'ja',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.languages.index', ['locale' => 'ja']))
            ->assertOk()
            ->assertSee('Hello')
            ->assertSee('こんにちは');

        $this->actingAs($admin)
            ->get(route('admin.languages.create'))
            ->assertOk()
            ->assertSee('<option value="ja" selected', false);

        $this->actingAs($admin)
            ->post(route('admin.languages.store'), [
                'locale' => 'ja',
                'key' => 'Goodbye',
                'value' => 'さようなら',
            ])
            ->assertRedirect(route('admin.languages.index', ['locale' => 'ja']));

        $this->actingAs($admin)
            ->post(route('admin.languages.store'), [
                'locale' => 'ja',
                'key' => 'Goodbye',
                'value' => 'さようなら',
            ])
            ->assertRedirect(route('admin.languages.index', ['locale' => 'ja']));

        $this->actingAs($admin)
            ->patch(route('admin.languages.update', ['locale' => 'ja', 'key' => 'Goodbye']), [
                'key' => 'See you',
                'value' => 'またね',
            ])
            ->assertRedirect(route('admin.languages.index', ['locale' => 'ja']));

        $this->actingAs($admin)
            ->delete(route('admin.languages.destroy', ['locale' => 'ja', 'key' => 'See you']))
            ->assertRedirect(route('admin.languages.index', ['locale' => 'ja']));

        $words = json_decode(
            file_get_contents($this->languageDirectory.DIRECTORY_SEPARATOR.'ja.json'),
            true,
        );
        $this->assertSame(['Hello' => 'こんにちは'], $words);

        $english = json_decode(
            file_get_contents($this->languageDirectory.DIRECTORY_SEPARATOR.'en.json'),
            true,
        );
        $spanish = json_decode(
            file_get_contents($this->languageDirectory.DIRECTORY_SEPARATOR.'es.json'),
            true,
        );
        $this->assertSame('Goodbye', $english['Goodbye']);
        $this->assertSame('Adiós', $spanish['Goodbye']);

        $cebuanoPath = $this->languageDirectory.DIRECTORY_SEPARATOR.'ceb.json';
        $cebuano = is_file($cebuanoPath)
            ? json_decode(file_get_contents($cebuanoPath), true)
            : [];
        $this->assertArrayNotHasKey('Goodbye', $cebuano);
    }

    public function test_language_words_are_paginated_twenty_per_page(): void
    {
        $words = [];
        for ($index = 1; $index <= 45; $index++) {
            $words[sprintf('Word %02d', $index)] = '言葉'.$index;
        }
        $this->app->make(LanguageFileWriter::class)->write('ja', $words);

        $admin = User::factory()->create(['role' => 'admin', 'preferred_language' => 'ja']);
        $response = $this->actingAs($admin)
            ->get(route('admin.languages.index', [
                'locale' => 'ja',
                'search' => 'Word',
                'page' => 2,
            ]))
            ->assertOk();

        $paginator = $response->viewData('words');
        $this->assertSame(45, $paginator->total());
        $this->assertSame(20, $paginator->perPage());
        $this->assertSame(2, $paginator->currentPage());
        $this->assertCount(20, $paginator);
        $response->assertSee('page=3', false);
        $response->assertSee('locale=ja', false);
        $response->assertSee('search=Word', false);
    }
}
