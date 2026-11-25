<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Support\LanguageFileWriter;
use Tests\TestCase;

class TranslationPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_page_is_available_to_guests(): void
    {
        $this->get(route('translate'))
            ->assertOk()
            ->assertSee('mirror-translation-form')
            ->assertSee('Detect language')
            ->assertSee('Sana ALL')
            ->assertSee('Community')
            ->assertSee('View All');
    }

    public function test_authenticated_translation_page_keeps_the_full_account_header(): void
    {
        $user = User::factory()->create([
            'username' => 'header_user',
        ]);

        $this->actingAs($user)
            ->get(route('translate'))
            ->assertOk()
            ->assertSee('header_user')
            ->assertSee(route('profile.show', $user), false)
            ->assertSee('fa-circle-user', false)
            ->assertSee('Bookmark')
            ->assertSee('Logout')
            ->assertSee('navbarDropdown');
    }

    public function test_translation_endpoint_proxies_to_libre_translate(): void
    {
        config([
            'services.libretranslate.url' => 'https://translate.test',
            'services.google_translate.enabled' => false,
        ]);
        Http::fake([
            'https://translate.test/detect' => Http::response([
                ['language' => 'en', 'confidence' => 0.99],
            ]),
            'https://translate.test/translate' => Http::response([
                'translatedText' => 'こんにちは',
            ]),
        ]);

        $this->postJson(route('translate.store'), [
            'text' => 'Hello',
            'source' => 'auto',
            'target' => 'ja',
        ])
            ->assertOk()
            ->assertJson([
                'translation' => 'こんにちは',
                'source' => 'auto',
                'target' => 'ja',
            ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://translate.test/translate'
            && $request['source'] === 'en'
            && $request['target'] === 'ja');
    }

    public function test_translation_endpoint_rejects_unsupported_languages(): void
    {
        $this->postJson(route('translate.store'), [
            'text' => 'Hello',
            'source' => 'auto',
            'target' => 'fr',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('target');
    }

    public function test_english_external_translation_is_added_to_supported_language_files(): void
    {
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'translation-catalog-'.uniqid();
        mkdir($directory);
        $this->app->instance(LanguageFileWriter::class, new LanguageFileWriter($directory));
        config([
            'services.libretranslate.url' => 'https://translate.test',
            'services.google_translate.enabled' => false,
        ]);
        Http::fake([
            'https://translate.test/translate' => fn ($request) => Http::response([
                'translatedText' => 'T:'.$request['target'],
            ]),
        ]);

        $this->postJson(route('translate.store'), [
            'text' => 'A new phrase',
            'source' => 'en',
            'target' => 'ja',
        ])->assertOk()
            ->assertJson(['translation' => 'T:ja']);

        $this->assertSame(
            'A new phrase',
            json_decode(file_get_contents($directory.'/en.json'), true)['A new phrase'],
        );
        $this->assertSame(
            'T:ja',
            json_decode(file_get_contents($directory.'/ja.json'), true)['A new phrase'],
        );
        $this->assertSame(
            'T:es',
            json_decode(file_get_contents($directory.'/es.json'), true)['A new phrase'],
        );

        array_map('unlink', glob($directory.DIRECTORY_SEPARATOR.'*.json'));
        rmdir($directory);
    }
}
