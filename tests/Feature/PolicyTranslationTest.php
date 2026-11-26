<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PolicyTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_translation_uses_the_viewers_preferred_language_and_caches_it(): void
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
                'translatedText' => 'イベントの規約',
            ]),
        ]);

        SiteSetting::put('policy_event', 'Organizers must provide accurate event details.');
        $reader = User::factory()->create(['preferred_language' => 'ja']);

        $request = fn () => $this->actingAs($reader)
            ->postJson(route('policies.translate', 'event'), ['source' => 'Ignored client text']);

        $request()
            ->assertOk()
            ->assertJson([
                'translation' => 'イベントの規約',
                'target_language' => 'ja',
            ]);

        $request()
            ->assertOk()
            ->assertJson([
                'translation' => 'イベントの規約',
                'target_language' => 'ja',
            ]);

        Http::assertSentCount(2);
        $translations = json_decode((string) SiteSetting::get('policy_translations_event'), true);
        $this->assertSame('イベントの規約', $translations['ja']);
    }

    public function test_default_policy_text_can_be_translated_without_becoming_the_saved_policy(): void
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
                'translatedText' => 'Términos de uso',
            ]),
        ]);

        $reader = User::factory()->create(['preferred_language' => 'es']);

        $this->actingAs($reader)
            ->postJson(route('policies.translate', 'terms'), [
                'source' => 'Default bilingual policy text.',
            ])
            ->assertOk()
            ->assertJson([
                'translation' => 'Términos de uso',
                'target_language' => 'es',
            ]);

        $this->assertNull(SiteSetting::get('policy_terms'));
        $this->assertNull(SiteSetting::get('policy_translations_terms'));
    }

    public function test_long_policy_text_is_translated_in_bounded_chunks(): void
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
                'translatedText' => '翻訳済み',
            ]),
        ]);

        SiteSetting::put('policy_p2p', str_repeat('Policy text. ', 300));
        $reader = User::factory()->create(['preferred_language' => 'ja']);

        $this->actingAs($reader)
            ->postJson(route('policies.translate', 'p2p'))
            ->assertOk()
            ->assertJsonPath('target_language', 'ja');

        Http::assertSentCount(4);
        Http::assertSent(fn ($request) => $request->url() === 'https://translate.test/translate'
            && mb_strlen($request['q'], 'UTF-8') <= 3000);
    }

    public function test_each_policy_modal_uses_a_translation_endpoint(): void
    {
        $this->withoutVite();

        $response = $this->get(route('translate'))->assertOk();

        foreach (['terms', 'guidelines', 'report', 'event', 'job', 'advertising', 'p2p'] as $suffix) {
            $response->assertSee(
                'data-policy-translation-url="'.route('policies.translate', $suffix).'"',
                false,
            );
        }
    }
}
