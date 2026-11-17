<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityNotificationReadTest extends TestCase
{
    use RefreshDatabase;

    private function joinRequestNotification(User $user, Community $community, bool $read = false): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'type' => 'community_join_request',
            'title' => 'Community join request',
            'related_type' => Community::class,
            'related_id' => $community->id,
            'read_at' => $read ? now() : null,
        ]);
    }

    public function test_navbar_lists_only_unread_community_notifications(): void
    {
        $owner = User::factory()->create();
        $fresh = $owner->communitiesOwned()->create(['name' => 'Fresh Club', 'description' => 'New.']);
        $old = $owner->communitiesOwned()->create(['name' => 'Old Club', 'description' => 'Seen.']);
        $this->joinRequestNotification($owner, $fresh);
        $this->joinRequestNotification($owner, $old, read: true);

        $this->actingAs($owner)
            ->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSee('Fresh Club')
            ->assertDontSee('Old Club');
    }

    public function test_navbar_tells_chat_messages_and_join_requests_apart(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create(['username' => 'chatty_member']);
        $applicant = User::factory()->create(['username' => 'eager_applicant']);
        $community = $owner->communitiesOwned()->create(['name' => 'Approval Club', 'description' => 'Ask first.', 'visibility' => 'approval']);
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        $this->actingAs($member)->postJson(route('community.chat.store', $community->id), ['message' => 'Hi'])->assertCreated();
        $this->travel(1)->minutes();
        $this->actingAs($applicant)->post(route('community.join', $community->id))->assertRedirect();

        $this->actingAs($owner)
            ->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSeeInOrder([
                'Approval Club', 'Join request from eager_applicant',
                'Approval Club', 'New message from chatty_member',
            ]);
    }

    public function test_opened_notification_disappears_from_the_navbar(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Fresh Club', 'description' => 'New.']);
        $notification = $this->joinRequestNotification($owner, $community);

        $this->actingAs($owner)->get(route('notifications.show', $notification))->assertRedirect();

        $this->actingAs($owner)
            ->get(route('profile.show', $owner->id))
            ->assertDontSee('Fresh Club')
            ->assertSee('No new community notifications');
    }

    public function test_reading_the_chat_marks_its_message_notifications_read(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Chat Club', 'description' => 'Talks.']);
        $otherCommunity = $owner->communitiesOwned()->create(['name' => 'Other Club', 'description' => 'Talks.']);
        foreach ([$member, $otherMember] as $user) {
            $community->members()->attach($user->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        }
        $otherCommunity->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        $first = $this->actingAs($member)
            ->postJson(route('community.chat.store', $community->id), ['message' => 'First'])
            ->json('message.id');
        $this->travel(1)->minutes();
        $second = $this->actingAs($member)
            ->postJson(route('community.chat.store', $community->id), ['message' => 'Second'])
            ->json('message.id');
        $this->actingAs($member)->postJson(route('community.chat.store', $otherCommunity->id), ['message' => 'Elsewhere']);

        $unread = fn (User $user, Community $in) => UserNotification::where('user_id', $user->id)
            ->where('type', 'community_message')
            ->where('related_id', $in->id)
            ->whereNull('read_at')
            ->count();
        $this->assertSame(2, $unread($owner, $community));

        // 1件目まで読んだら、1件目の分だけ既読になる
        $this->actingAs($owner)->postJson(route('community.chat.read', $community->id), ['last_read_id' => $first])->assertOk();
        $this->assertSame(1, $unread($owner, $community));

        // 最後まで読んだら全部既読。ほかのコミュニティや、ほかの人の通知はそのまま
        $this->actingAs($owner)->postJson(route('community.chat.read', $community->id), ['last_read_id' => $second])->assertOk();
        $this->assertSame(0, $unread($owner, $community));
        $this->assertSame(1, $unread($owner, $otherCommunity));
        $this->assertSame(2, $unread($otherMember, $community));
    }

    public function test_notifications_for_deleted_messages_clear_once_the_chat_is_read(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Chat Club', 'description' => 'Talks.']);
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        $kept = $this->actingAs($owner)
            ->postJson(route('community.chat.store', $community->id), ['message' => 'Kept'])
            ->json('message.id');
        $this->travel(1)->minutes();
        $removed = $this->actingAs($owner)
            ->postJson(route('community.chat.store', $community->id), ['message' => 'Oops'])
            ->json('message.id');
        $this->actingAs($owner)->deleteJson(route('community.chat.destroy', [$community->id, $removed]))->assertOk();

        // 削除されたメッセージは画面に出ないので、表示された最後のメッセージまで読めば通知は全部消える
        // ナビバーの数字を更新するための件数も 0 で返る
        $this->actingAs($member)
            ->postJson(route('community.chat.read', $community->id), ['last_read_id' => $kept])
            ->assertJsonPath('unread', 0)
            ->assertJsonPath('community_notification_count', 0);

        $this->assertSame(0, UserNotification::where('user_id', $member->id)->whereNull('read_at')->count());
    }
}
