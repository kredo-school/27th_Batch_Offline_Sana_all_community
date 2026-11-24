<?php

namespace Tests\Feature;

use App\Support\LanguageFileWriter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class MissingTranslationTest extends TestCase
{
    use RefreshDatabase;

    private string $languageDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.supported_locales' => [
            'en' => 'English',
            'ja' => 'Japanese',
            'es' => 'Spanish',
        ]]);
        config(['services.libretranslate.url' => 'https://translate.test']);
        config(['services.google_translate.enabled' => false]);

        $this->languageDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'missing-translations-'.uniqid();
        mkdir($this->languageDirectory);

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            file_put_contents(
                $this->languageDirectory.DIRECTORY_SEPARATOR.$locale.'.json',
                json_encode($locale === 'ja' ? ['Existing' => '既存'] : [], JSON_UNESCAPED_UNICODE)."\n",
            );
        }

        $this->app->instance(
            LanguageFileWriter::class,
            new LanguageFileWriter($this->languageDirectory),
        );
    }

    protected function tearDown(): void
    {
        foreach (glob($this->languageDirectory.DIRECTORY_SEPARATOR.'*') as $file) {
            unlink($file);
        }
        rmdir($this->languageDirectory);

        parent::tearDown();
    }

    public function test_missing_json_key_is_translated_and_persisted_for_every_locale(): void
    {
        Http::fake(fn ($request) => Http::response([
            'translatedText' => match ($request['target']) {
                'ja' => '新しい文章',
                'es' => 'Frase nueva',
                default => 'New sentence',
            },
        ]));

        App::setLocale('ja');

        $this->assertSame('新しい文章', Lang::get('New sentence'));

        $english = json_decode(file_get_contents($this->languageDirectory.'/en.json'), true);
        $japanese = json_decode(file_get_contents($this->languageDirectory.'/ja.json'), true);
        $spanish = json_decode(file_get_contents($this->languageDirectory.'/es.json'), true);

        $this->assertSame('New sentence', $english['New sentence']);
        $this->assertSame('既存', $japanese['Existing']);
        $this->assertSame('新しい文章', $japanese['New sentence']);
        $this->assertSame('Frase nueva', $spanish['New sentence']);
        Http::assertSentCount(2);
    }

    public function test_non_english_source_key_is_not_added_to_locale_files(): void
    {
        App::setLocale('ja');

        $this->assertSame('スポーツ', Lang::get('スポーツ'));

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            $values = json_decode(
                file_get_contents($this->languageDirectory.DIRECTORY_SEPARATOR.$locale.'.json'),
                true,
            );

            $this->assertArrayNotHasKey('スポーツ', $values);
        }
    }
}
