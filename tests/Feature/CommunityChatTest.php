<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityChatTest extends TestCase
{
    use RefreshDatabase;

    private function communityWithMembers(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['username' => 'chat_member']);
        $pending = User::factory()->create();
        $outsider = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Chat Club', 'description' => 'Talks.']);
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $community->members()->attach($pending->id, ['role' => 'member', 'status' => 'pending']);

        return [$community, $owner, $member, $pending, $outsider];
    }

    public function test_members_can_send_and_read_messages(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();

        $this->actingAs($member)
            ->postJson(route('community.chat.store', $community->id), ['message' => 'Hello everyone'])
            ->assertCreated()
            ->assertJsonPath('message.body', 'Hello everyone')
            ->assertJsonPath('message.is_mine', true)
            ->assertJsonPath('message.sender.name', 'chat_member');

        $this->actingAs($owner)
            ->getJson(route('community.chat.index', $community->id))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.body', 'Hello everyone')
            ->assertJsonPath('messages.0.is_mine', false);
    }

    public function test_after_parameter_returns_only_newer_messages(): void
    {
        [$community, $owner] = $this->communityWithMembers();
        $first = $community->messages()->create(['user_id' => $owner->id, 'message' => 'First']);
        $community->messages()->create(['user_id' => $owner->id, 'message' => 'Second']);

        $this->actingAs($owner)
            ->getJson(route('community.chat.index', [$community->id, 'after' => $first->id]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.body', 'Second');
    }

    public function test_non_members_cannot_read_or_send(): void
    {
        [$community, , , $pending, $outsider] = $this->communityWithMembers();

        foreach ([$pending, $outsider] as $user) {
            $this->actingAs($user)
                ->getJson(route('community.chat.index', $community->id))
                ->assertForbidden();
            $this->actingAs($user)
                ->postJson(route('community.chat.store', $community->id), ['message' => 'Let me in'])
                ->assertForbidden();
        }

        $this->assertSame(0, $community->messages()->count());
    }

    public function test_author_and_owner_can_delete_but_other_members_cannot(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();
        $other = User::factory()->create();
        $community->members()->attach($other->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $first = $community->messages()->create(['user_id' => $member->id, 'message' => 'Mine']);
        $second = $community->messages()->create(['user_id' => $member->id, 'message' => 'Moderated']);

        $this->actingAs($other)
            ->deleteJson(route('community.chat.destroy', [$community->id, $first->id]))
            ->assertForbidden();

        $this->actingAs($member)
            ->deleteJson(route('community.chat.destroy', [$community->id, $first->id]))
            ->assertOk()
            ->assertJsonPath('deleted', $first->id);

        $this->actingAs($owner)
            ->deleteJson(route('community.chat.destroy', [$community->id, $second->id]))
            ->assertOk();

        $this->assertSame(0, $community->messages()->count());
    }

    public function test_polling_reports_messages_deleted_since_last_sync(): void
    {
        [$community, $owner] = $this->communityWithMembers();
        $message = $community->messages()->create(['user_id' => $owner->id, 'message' => 'Soon gone']);

        $syncedAt = $this->actingAs($owner)
            ->getJson(route('community.chat.index', $community->id))
            ->assertJsonPath('deleted_ids', [])
            ->json('synced_at');

        $message->delete();

        $this->actingAs($owner)
            ->getJson(route('community.chat.index', [$community->id, 'after' => $message->id, 'since' => $syncedAt]))
            ->assertJsonCount(0, 'messages')
            ->assertJsonPath('deleted_ids', [$message->id]);
    }

    public function test_empty_message_is_rejected(): void
    {
        [$community, $owner] = $this->communityWithMembers();

        $this->actingAs($owner)
            ->postJson(route('community.chat.store', $community->id), ['message' => ''])
            ->assertUnprocessable();
    }

    public function test_chat_tab_shows_form_only_to_members(): void
    {
        [$community, $owner, , , $outsider] = $this->communityWithMembers();

        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertSee(route('community.chat.store', $community->id), false);

        $this->actingAs($outsider)
            ->get(route('community.show', $community->id))
            ->assertDontSee(route('community.chat.store', $community->id), false)
            ->assertSee('Only members of this community can join the chat.');
    }
}
