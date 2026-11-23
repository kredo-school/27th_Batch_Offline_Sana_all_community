<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityChatImageTest extends TestCase
{
    use RefreshDatabase;

    private function community(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Photo Club', 'description' => 'Photos.']);
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        return [$community, $owner, $member];
    }

    public function test_a_member_can_send_a_photo_with_or_without_text(): void
    {
        Storage::fake('public');
        [$community, $owner, $member] = $this->community();

        $response = $this->actingAs($member)
            ->post(route('community.chat.store', $community->id), [
                'image' => UploadedFile::fake()->image('practice.jpg', 600, 400),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('message.body', '');

        $message = $community->messages()->sole();
        Storage::disk('public')->assertExists($message->image);
        $this->assertSame(asset('storage/' . $message->image), $response->json('message.image_url'));

        $this->actingAs($member)
            ->post(route('community.chat.store', $community->id), [
                'message' => 'Flyer for Saturday',
                'image' => UploadedFile::fake()->image('flyer.png'),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Flyer for Saturday');

        // 画像だけのメッセージは、チャット一覧で「Photo」と出る
        $this->actingAs($owner)
            ->get(route('community.chats'))
            ->assertSee('📷 Photo');
    }

    public function test_empty_messages_and_non_images_are_rejected(): void
    {
        Storage::fake('public');
        [$community, , $member] = $this->community();

        $this->actingAs($member)
            ->postJson(route('community.chat.store', $community->id), ['message' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('message');

        $this->actingAs($member)
            ->post(route('community.chat.store', $community->id), [
                'image' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');

        $this->assertSame(0, $community->messages()->count());
    }

    public function test_the_text_of_a_photo_message_can_be_cleared(): void
    {
        [$community, , $member] = $this->community();
        $photo = $community->messages()->create(['user_id' => $member->id, 'message' => 'Typo', 'image' => 'communities/chat/a.jpg']);
        $text = $community->messages()->create(['user_id' => $member->id, 'message' => 'Hello']);

        $this->actingAs($member)
            ->patchJson(route('community.chat.update', [$community->id, $photo->id]), ['message' => ''])
            ->assertOk()
            ->assertJsonPath('message.body', '');

        // 文字だけのメッセージは空にできない
        $this->actingAs($member)
            ->patchJson(route('community.chat.update', [$community->id, $text->id]), ['message' => ''])
            ->assertUnprocessable();
    }
}
