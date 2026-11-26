<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MessageTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_preference_translates_and_refreshes_stale_message_cache(): void
    {
        config([
            'services.libretranslate.url' => 'https://translate.test',
            'services.google_translate.enabled' => false,
        ]);
        Http::fake([
            'https://translate.test/detect' => Http::response([
                ['language' => 'ja', 'confidence' => 0.99],
            ]),
            'https://translate.test/translate' => fn ($request) => Http::response([
                'translatedText' => $request['q'] === 'こんにちは' ? 'Hello' : 'Goodbye',
            ]),
        ]);

        $reader = User::factory()->create(['preferred_language' => 'en']);
        $sender = User::factory()->create(['preferred_language' => 'ja']);
        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $reader->id,
            'message' => 'こんにちは',
        ]);
        Translation::create([
            'translatable_type' => 'message',
            'translatable_id' => $message->id,
            'field' => 'message',
            'source_language' => 'auto',
            'target_language' => 'en',
            'translated_text' => 'こんにちは',
        ]);

        $this->actingAs($reader)
            ->postJson(route('messages.translate', $message))
            ->assertOk()
            ->assertJson([
                'translation' => 'Hello',
                'target_language' => 'en',
            ]);

        $message->update(['message' => 'さようなら']);

        $this->postJson(route('messages.translate', $message))
            ->assertOk()
            ->assertJson([
                'translation' => 'Goodbye',
                'target_language' => 'en',
            ]);

        Http::assertSentCount(4);
        Http::assertSent(fn ($request) => $request->url() === 'https://translate.test/translate'
            && $request['source'] === 'ja'
            && $request['target'] === 'en');
        $this->assertDatabaseHas('translations', [
            'translatable_type' => 'message',
            'translatable_id' => $message->id,
            'field' => 'message',
            'target_language' => 'en',
            'translated_text' => 'Goodbye',
            'source_hash' => hash('sha256', "username-handles-v1\0さようなら"),
        ]);
    }

    public function test_chat_translation_preserves_username_handles(): void
    {
        config([
            'services.libretranslate.url' => 'https://translate.test',
            'services.google_translate.enabled' => false,
        ]);
        Http::fake([
            'https://translate.test/detect' => Http::response([
                ['language' => 'es', 'confidence' => 0.99],
            ]),
            'https://translate.test/translate' => fn ($request) => Http::response([
                'translatedText' => str_replace('Hola', 'Hello', $request['q']),
            ]),
        ]);

        $reader = User::factory()->create(['preferred_language' => 'en']);
        $community = $reader->communitiesOwned()->create([
            'name' => 'Translation Club',
            'description' => 'Messages with usernames.',
        ]);
        $message = $community->messages()->create([
            'user_id' => $reader->id,
            'message' => 'Hola, @chat_member and @other-user!',
        ]);

        $this->actingAs($reader)
            ->postJson(route('community.chat.translate', [$community->id, $message->id]))
            ->assertOk()
            ->assertJson([
                'translation' => 'Hello, @chat_member and @other-user!',
                'target_language' => 'en',
            ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://translate.test/translate'
            && ! str_contains($request['q'], '@chat_member')
            && ! str_contains($request['q'], '@other-user'));
    }
}
