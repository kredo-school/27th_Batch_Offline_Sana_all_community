<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_user_can_block_a_conversation_participant_and_cannot_send_afterward(): void
    {
        $user = User::factory()->create();
        $participant = User::factory()->create();
        $message = Message::create([
            'sender_id' => $participant->id,
            'receiver_id' => $user->id,
            'message' => 'Hello there',
        ]);

        $this->actingAs($user)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee(route('messages.block', $participant), false)
            ->assertSee(route('messages.destroy', $message), false);

        $this->post(route('messages.block', $participant))
            ->assertRedirect();

        $this->assertDatabaseHas('user_blocks', [
            'user_id' => $participant->id,
            'blocked_by' => $user->id,
        ]);

        $this->getJson(route('messages.conversation', $participant))
            ->assertOk()
            ->assertJsonPath('can_send', false);

        $this->postJson(route('messages.send', $participant), ['message' => 'Another message'])
            ->assertForbidden();
    }

    public function test_conversation_participant_can_soft_delete_a_message(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => 'Delete me',
        ]);

        $this->actingAs($receiver)
            ->delete(route('messages.destroy', $message))
            ->assertRedirect();

        $this->assertSoftDeleted($message);
    }

    public function test_layout_displays_unread_received_message_count(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $other = User::factory()->create();

        Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $user->id,
            'message' => 'Unread incoming',
        ]);
        Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $sender->id,
            'message' => 'Outgoing message',
        ]);
        Message::create([
            'sender_id' => $other->id,
            'receiver_id' => $user->id,
            'message' => 'Read incoming',
            'read_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('>1</span>', false);

        $this->getJson(route('messages.conversation', $sender))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertDatabaseMissing('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $user->id,
            'read_at' => null,
        ]);
        $this->get(route('messages.index'))
            ->assertOk()
            ->assertViewHas('unreadMessagesCount', 0);
    }

    public function test_user_can_unblock_a_participant_from_messages_and_profile(): void
    {
        $user = User::factory()->create();
        $participant = User::factory()->create();
        Message::create([
            'sender_id' => $participant->id,
            'receiver_id' => $user->id,
            'message' => 'Hello there',
        ]);

        $this->actingAs($user)
            ->post(route('messages.block', $participant))
            ->assertRedirect();

        $this->get(route('profile.show', $participant))
            ->assertOk()
            ->assertSee('Unblock user');

        $this->delete(route('messages.unblock', $participant))
            ->assertRedirect();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id' => $participant->id,
            'blocked_by' => $user->id,
        ]);

        $this->getJson(route('messages.conversation', $participant))
            ->assertOk()
            ->assertJsonPath('can_send', true);
    }

    public function test_admin_can_block_a_message_sender_and_delete_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => 'Reported message',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.messages'))
            ->assertOk()
            ->assertSee(route('admin.messages.block', $sender), false)
            ->assertSee(route('admin.messages.destroy', $message), false);

        $this->post(route('admin.messages.block', $sender))
            ->assertRedirect();

        $this->assertDatabaseHas('user_blocks', [
            'user_id' => $sender->id,
            'blocked_by' => $admin->id,
        ]);

        $this->delete(route('admin.messages.destroy', $message))
            ->assertRedirect();

        $this->assertSoftDeleted($message);
    }

    public function test_admin_can_view_conversation_between_sender_and_receiver(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $other = User::factory()->create();
        $first = Message::create(['sender_id' => $sender->id, 'receiver_id' => $receiver->id, 'message' => 'Hello there']);
        Message::create(['sender_id' => $sender->id, 'receiver_id' => $receiver->id, 'message' => 'Second hello']);
        $reply = Message::create(['sender_id' => $receiver->id, 'receiver_id' => $sender->id, 'message' => 'Reply back']);
        Message::create(['sender_id' => $other->id, 'receiver_id' => $receiver->id, 'message' => 'Unrelated chat']);

        $this->actingAs($admin)
            ->get(route('admin.messages'))
            ->assertOk()
            ->assertSee(route('admin.messages.show', $reply), false)
            ->assertDontSee(route('admin.messages.show', $first), false)
            ->assertViewHas('records', fn ($records) => $records->total() === 2
                && $records->firstWhere('id', $reply->id)->messages_count === 3);

        $this->get(route('admin.messages.show', $reply))
            ->assertOk()
            ->assertSee('Hello there')
            ->assertSee('Second hello')
            ->assertSee('Reply back')
            ->assertDontSee('Unrelated chat');
    }

    public function test_admin_can_unblock_a_sender_from_message_moderation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $sender = User::factory()->create();
        Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => User::factory()->create()->id,
            'message' => 'Reported message',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.messages.block', $sender))
            ->assertRedirect();

        $this->get(route('admin.messages'))
            ->assertOk()
            ->assertSee(route('admin.messages.unblock', $sender), false)
            ->assertSee('fa-square-plus', false);

        $this->delete(route('admin.messages.unblock', $sender))
            ->assertRedirect();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id' => $sender->id,
            'blocked_by' => $admin->id,
        ]);
    }
}
