<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminContactTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_translate_contact_details_to_their_preferred_language(): void
    {
        config([
            'services.libretranslate.url' => 'https://translate.test',
            'services.google_translate.enabled' => false,
        ]);
        Http::fake([
            'https://translate.test/detect' => Http::response([
                ['language' => 'ja', 'confidence' => 0.99],
            ]),
            'https://translate.test/translate' => Http::response([
                'translatedText' => 'Mensaje de contacto',
            ]),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'es',
        ]);
        $contact = Contact::create([
            'name' => 'Contact Sender',
            'username' => 'contact_sender',
            'email' => 'sender@example.com',
            'detail' => 'お問い合わせ内容',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.contacts.translate', $contact))
            ->assertOk()
            ->assertJson([
                'translation' => 'Mensaje de contacto',
                'target_language' => 'es',
            ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://translate.test/translate'
            && $request['source'] === 'ja'
            && $request['target'] === 'es');
    }

    public function test_contact_details_page_shows_a_translation_control(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $contact = Contact::create([
            'name' => 'Contact Sender',
            'username' => 'contact_sender',
            'email' => 'sender@example.com',
            'detail' => 'Contact details.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contacts.show', $contact))
            ->assertOk()
            ->assertSee(route('admin.contacts.translate', $contact), false)
            ->assertSee('content-translate-button', false)
            ->assertSee('content-translation-result', false);
    }
}
