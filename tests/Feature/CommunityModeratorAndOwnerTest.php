<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityModeratorAndOwnerTest extends TestCase
{
    use RefreshDatabase;

    private function community(string $visibility = 'approval'): array
    {
        $owner = User::factory()->create();
        $helper = User::factory()->create(['name' => 'Helper Hana']);
        $member = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Ball Club', 'description' => 'Ball.', 'visibility' => $visibility]);
        foreach ([$helper, $member] as $user) {
            $community->members()->attach($user->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        }

        return [$community, $owner, $helper, $member];
    }

    private function role(Community $community, User $user): ?string
    {
        return $community->memberships()->where('user_id', $user->id)->value('role');
    }

    public function test_owner_can_make_and_remove_a_moderator(): void
    {
        [$community, $owner, $helper] = $this->community();

        $this->actingAs($owner)
            ->patch(route('community.members.role', [$community->id, $helper->id]), ['role' => 'moderator'])
            ->assertRedirect(route('community.show', $community->id) . '#members');
        $this->assertSame('moderator', $this->role($community, $helper));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $helper->id,
            'type' => 'community_moderator_added',
            'related_id' => $community->id,
        ]);

        $this->actingAs($owner)
            ->patch(route('community.members.role', [$community->id, $helper->id]), ['role' => 'member']);
        $this->assertSame('member', $this->role($community, $helper));
    }

    public function test_only_the_owner_can_change_roles(): void
    {
        [$community, , $helper, $member] = $this->community();
        $community->memberships()->where('user_id', $helper->id)->update(['role' => 'moderator']);

        // 副管理者でも、他の人を副管理者にはできない
        $this->actingAs($helper)
            ->patch(route('community.members.role', [$community->id, $member->id]), ['role' => 'moderator'])
            ->assertForbidden();
        $this->assertSame('member', $this->role($community, $member));
    }

    public function test_moderator_gets_join_requests_and_can_approve_and_decline(): void
    {
        [$community, $owner, $helper] = $this->community();
        $community->memberships()->where('user_id', $helper->id)->update(['role' => 'moderator']);
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->post(route('community.join', $community->id));
        $this->actingAs($second)->post(route('community.join', $community->id));

        // 参加申請はオーナーと副管理者の両方に届く
        foreach ([$owner, $helper] as $manager) {
            $this->assertSame(2, UserNotification::where('user_id', $manager->id)->where('type', 'community_join_request')->count());
        }

        $this->actingAs($helper)
            ->get(route('community.show', $community->id))
            ->assertSee('Join requests')
            ->assertSee('Moderator');

        $this->actingAs($helper)
            ->patch(route('community.members.approve', [$community->id, $first->id]))
            ->assertRedirect();
        $this->actingAs($helper)
            ->delete(route('community.members.remove', [$community->id, $second->id]))
            ->assertRedirect();

        $this->assertSame('approved', $community->memberships()->where('user_id', $first->id)->value('status'));
        $this->assertFalse($community->memberships()->where('user_id', $second->id)->exists());
        // 片付けた申請の通知は、オーナー側でも既読になる
        $this->assertSame(0, UserNotification::where('user_id', $owner->id)->where('type', 'community_join_request')->whereNull('read_at')->count());
    }

    public function test_moderator_cannot_remove_members_but_can_delete_any_chat_message(): void
    {
        [$community, , $helper, $member] = $this->community();
        $community->memberships()->where('user_id', $helper->id)->update(['role' => 'moderator']);
        $message = $community->messages()->create(['user_id' => $member->id, 'message' => 'Spam']);

        $this->actingAs($helper)
            ->getJson(route('community.chat.index', $community->id))
            ->assertJsonPath('messages.0.can_delete', true);

        $this->actingAs($helper)
            ->deleteJson(route('community.chat.destroy', [$community->id, $message->id]))
            ->assertOk();
        $this->assertSoftDeleted($message);

        // メンバーを外せるのはオーナーだけ
        $this->actingAs($helper)
            ->delete(route('community.members.remove', [$community->id, $member->id]))
            ->assertForbidden();
    }

    public function test_regular_members_still_cannot_delete_others_messages(): void
    {
        [$community, , $helper, $member] = $this->community();
        $message = $community->messages()->create(['user_id' => $member->id, 'message' => 'Hi']);

        $this->actingAs($helper)
            ->getJson(route('community.chat.index', $community->id))
            ->assertJsonPath('messages.0.can_delete', false);
        $this->actingAs($helper)
            ->deleteJson(route('community.chat.destroy', [$community->id, $message->id]))
            ->assertForbidden();
    }

    public function test_owner_can_transfer_ownership_to_a_member(): void
    {
        [$community, $owner, $helper] = $this->community();

        $this->actingAs($owner)
            ->patch(route('community.owner.transfer', $community->id), ['user_id' => $helper->id])
            ->assertRedirect(route('community.show', $community->id) . '#members');

        $community->refresh();
        $this->assertSame($helper->id, $community->owner_id);
        // 新しいオーナーはメンバーの行を持たず、元のオーナーは普通のメンバーとして残る
        $this->assertFalse($community->memberships()->where('user_id', $helper->id)->exists());
        $this->assertSame('approved', $community->memberships()->where('user_id', $owner->id)->value('status'));
        $this->assertSame('member', $this->role($community, $owner));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $helper->id,
            'type' => 'community_ownership_transferred',
        ]);

        // 元のオーナーはもう編集できず、新しいオーナーは編集できる
        $this->actingAs($owner)->get(route('community.edit', $community->id))->assertForbidden();
        $this->actingAs($helper)->get(route('community.edit', $community->id))->assertOk();
        // メンバーの数は変わらない（オーナー + 2 人）
        $this->actingAs($helper)->get(route('community.show', $community->id))
            ->assertViewHas('memberCount', 3);
    }

    public function test_ownership_can_only_go_to_an_approved_member(): void
    {
        [$community, $owner, $helper] = $this->community();
        $pending = User::factory()->create();
        $community->members()->attach($pending->id, ['role' => 'member', 'status' => 'pending']);
        $outsider = User::factory()->create();

        foreach ([$pending, $outsider] as $user) {
            $this->actingAs($owner)
                ->patch(route('community.owner.transfer', $community->id), ['user_id' => $user->id])
                ->assertNotFound();
        }

        // オーナー以外は引き継ぎできない
        $this->actingAs($helper)
            ->patch(route('community.owner.transfer', $community->id), ['user_id' => $helper->id])
            ->assertForbidden();
        $this->assertSame($owner->id, $community->fresh()->owner_id);
    }
}
