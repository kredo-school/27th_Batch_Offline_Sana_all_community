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
            ->assertJsonPath('message.sender.id', $member->id)
            ->assertJsonPath('message.sender.name', 'chat_member')
            ->assertJsonPath('message.sender.profile_url', route('profile.show', $member->id));

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

    public function test_author_can_edit_their_message(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();
        $message = $community->messages()->create(['user_id' => $member->id, 'message' => 'Helo']);

        $this->actingAs($member)
            ->getJson(route('community.chat.index', $community->id))
            ->assertJsonPath('messages.0.is_edited', false)
            ->assertJsonPath('messages.0.can_edit', true);

        $this->travel(1)->minutes();

        $this->actingAs($member)
            ->patchJson(route('community.chat.update', [$community->id, $message->id]), ['message' => '  Hello  '])
            ->assertOk()
            ->assertJsonPath('message.body', 'Hello')
            ->assertJsonPath('message.is_edited', true);

        $this->assertSame('Hello', $message->fresh()->message);

        // オーナーには編集ボタンは出ないが、削除はできる
        $this->actingAs($owner)
            ->getJson(route('community.chat.index', $community->id))
            ->assertJsonPath('messages.0.can_edit', false)
            ->assertJsonPath('messages.0.can_delete', true);
    }

    public function test_only_the_author_can_edit(): void
    {
        [$community, $owner, $member, $pending, $outsider] = $this->communityWithMembers();
        $message = $community->messages()->create(['user_id' => $member->id, 'message' => 'Original']);

        foreach ([$owner, $pending, $outsider] as $user) {
            $this->actingAs($user)
                ->patchJson(route('community.chat.update', [$community->id, $message->id]), ['message' => 'Changed'])
                ->assertForbidden();
        }

        // 退会した本人も編集できない
        $community->memberships()->where('user_id', $member->id)->delete();
        $this->actingAs($member)
            ->patchJson(route('community.chat.update', [$community->id, $message->id]), ['message' => 'Changed'])
            ->assertForbidden();

        $this->assertSame('Original', $message->fresh()->message);
    }

    public function test_edit_must_not_be_empty_or_target_another_community(): void
    {
        [$community, , $member] = $this->communityWithMembers();
        $message = $community->messages()->create(['user_id' => $member->id, 'message' => 'Original']);
        $otherCommunity = $member->communitiesOwned()->create(['name' => 'Other Club', 'description' => 'Other.']);

        $this->actingAs($member)
            ->patchJson(route('community.chat.update', [$community->id, $message->id]), ['message' => ''])
            ->assertUnprocessable();

        $this->actingAs($member)
            ->patchJson(route('community.chat.update', [$otherCommunity->id, $message->id]), ['message' => 'Changed'])
            ->assertNotFound();

        $this->assertSame('Original', $message->fresh()->message);
    }

    public function test_polling_reports_messages_edited_since_last_sync_without_making_them_unread(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();
        $message = $community->messages()->create(['user_id' => $member->id, 'message' => 'Helo']);

        $syncedAt = $this->actingAs($owner)
            ->getJson(route('community.chat.index', $community->id))
            ->assertJsonPath('updated_messages', [])
            ->json('synced_at');
        $this->actingAs($owner)
            ->postJson(route('community.chat.read', $community->id), ['last_read_id' => $message->id]);

        $this->travel(1)->minutes();
        $this->actingAs($member)
            ->patchJson(route('community.chat.update', [$community->id, $message->id]), ['message' => 'Hello']);

        $this->actingAs($owner)
            ->getJson(route('community.chat.index', [$community->id, 'after' => $message->id, 'since' => $syncedAt]))
            ->assertJsonCount(0, 'messages')
            ->assertJsonCount(1, 'updated_messages')
            ->assertJsonPath('updated_messages.0.body', 'Hello');

        $this->actingAs($owner)
            ->getJson(route('community.chat.unread', $community->id))
            ->assertJsonPath('unread', 0);
    }

    public function test_empty_message_is_rejected(): void
    {
        [$community, $owner] = $this->communityWithMembers();

        $this->actingAs($owner)
            ->postJson(route('community.chat.store', $community->id), ['message' => ''])
            ->assertUnprocessable();
    }

    public function test_unread_count_excludes_own_and_deleted_messages(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();
        $community->messages()->create(['user_id' => $member->id, 'message' => 'For the owner']);
        $community->messages()->create(['user_id' => $owner->id, 'message' => 'My own']);
        $community->messages()->create(['user_id' => $member->id, 'message' => 'Removed'])->delete();

        $this->actingAs($owner)
            ->getJson(route('community.chat.unread', $community->id))
            ->assertOk()
            ->assertJsonPath('unread', 1);
    }

    public function test_unread_lists_each_sender_once_with_the_latest_first(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();
        $other = User::factory()->create(['username' => 'other_member']);
        $community->members()->attach($other->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $community->messages()->create(['user_id' => $member->id, 'message' => 'One']);
        $community->messages()->create(['user_id' => $other->id, 'message' => 'Two']);
        $community->messages()->create(['user_id' => $member->id, 'message' => 'Three']);
        $community->messages()->create(['user_id' => $owner->id, 'message' => 'My own']);

        $this->actingAs($owner)
            ->getJson(route('community.chat.unread', $community->id))
            ->assertJsonPath('unread', 3)
            ->assertJsonPath('senders', ['chat_member', 'other_member']);

        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertSee('<span class="community-chat-unread-name">chat_member</span>', false)
            ->assertSee('<span class="community-chat-unread-more"> +1</span>', false);
    }

    public function test_marking_read_clears_unread_until_a_new_message_arrives(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();
        $message = $community->messages()->create(['user_id' => $member->id, 'message' => 'Hello']);

        $this->actingAs($owner)
            ->postJson(route('community.chat.read', $community->id), ['last_read_id' => $message->id])
            ->assertOk()
            ->assertJsonPath('unread', 0);

        $community->messages()->create(['user_id' => $member->id, 'message' => 'Hello again']);

        $this->actingAs($owner)
            ->getJson(route('community.chat.unread', $community->id))
            ->assertJsonPath('unread', 1);

        // 既読の状態は他のメンバーに影響しない
        $this->actingAs($member)
            ->getJson(route('community.chat.unread', $community->id))
            ->assertJsonPath('unread', 0);
    }

    public function test_read_position_never_moves_back_or_past_the_latest_message(): void
    {
        [$community, $owner, $member] = $this->communityWithMembers();
        $first = $community->messages()->create(['user_id' => $member->id, 'message' => 'First']);
        $second = $community->messages()->create(['user_id' => $member->id, 'message' => 'Second']);

        $this->actingAs($owner)
            ->postJson(route('community.chat.read', $community->id), ['last_read_id' => $second->id])
            ->assertJsonPath('unread', 0);

        // 古い位置を送っても戻らない
        $this->actingAs($owner)
            ->postJson(route('community.chat.read', $community->id), ['last_read_id' => $first->id])
            ->assertJsonPath('unread', 0);

        // 存在しない大きな ID を送っても、この後の新着は隠れない
        $this->actingAs($owner)
            ->postJson(route('community.chat.read', $community->id), ['last_read_id' => $second->id + 1000])
            ->assertJsonPath('unread', 0);
        $community->messages()->create(['user_id' => $member->id, 'message' => 'Third']);

        $this->actingAs($owner)
            ->getJson(route('community.chat.unread', $community->id))
            ->assertJsonPath('unread', 1);
    }

    public function test_non_members_cannot_read_unread_count_or_mark_read(): void
    {
        [$community, , , $pending, $outsider] = $this->communityWithMembers();

        foreach ([$pending, $outsider] as $user) {
            $this->actingAs($user)
                ->getJson(route('community.chat.unread', $community->id))
                ->assertForbidden();
            $this->actingAs($user)
                ->postJson(route('community.chat.read', $community->id), ['last_read_id' => 1])
                ->assertForbidden();
        }
    }

    public function test_chat_tab_shows_unread_badge_for_members_only(): void
    {
        [$community, $owner, $member, , $outsider] = $this->communityWithMembers();
        $community->messages()->create(['user_id' => $member->id, 'message' => 'Hello']);

        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertSee('<span class="community-chat-unread-count">1</span>', false);

        // 自分の発言だけなら未読なし（バッジは隠れる）
        $this->actingAs($member)
            ->get(route('community.show', $community->id))
            ->assertSee('<span class="community-chat-unread-count">0</span>', false);

        $this->actingAs($outsider)
            ->get(route('community.show', $community->id))
            ->assertDontSee('community-chat-unread-count', false);
    }

    public function test_organizer_and_members_link_to_their_profiles(): void
    {
        [$community, $owner, $member, , $outsider] = $this->communityWithMembers();

        $this->actingAs($outsider)
            ->get(route('community.show', $community->id))
            ->assertSee('href="' . route('profile.show', $owner->id) . '"', false)
            ->assertSee('href="' . route('profile.show', $member->id) . '"', false);
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
