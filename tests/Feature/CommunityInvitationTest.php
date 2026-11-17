<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function privateCommunity(): array
    {
        $owner = User::factory()->create(['username' => 'club_owner']);
        $invitee = User::factory()->create(['username' => 'lucky_friend']);
        $community = $owner->communitiesOwned()->create([
            'name' => 'Secret Club', 'description' => 'Invite only.', 'visibility' => 'private',
        ]);

        return [$community, $owner, $invitee];
    }

    private function membershipStatus(Community $community, User $user): ?string
    {
        return $community->memberships()->where('user_id', $user->id)->value('status');
    }

    public function test_owner_invites_and_the_invitee_accepts(): void
    {
        [$community, $owner, $invitee] = $this->privateCommunity();

        $this->actingAs($owner)
            ->post(route('community.invitations.store', $community->id), ['username' => 'lucky_friend'])
            ->assertRedirect(route('community.show', $community->id) . '#members')
            ->assertSessionMissing('_old_input'); // 成功したら入力欄は空に戻る

        $this->assertSame('invited', $this->membershipStatus($community, $invitee));
        $notification = UserNotification::where('user_id', $invitee->id)->sole();
        $this->assertSame('community_invite', $notification->type);
        $this->assertSame('club_owner', $notification->sender_name);

        // 承諾する前はまだメンバーではない（チャットも使えない）
        $this->assertFalse($community->hasMember($invitee));
        $this->actingAs($invitee)
            ->get(route('community.show', $community->id))
            ->assertSee('Accept invitation')
            ->assertSee('Invitation from club_owner');

        $this->actingAs($invitee)
            ->patch(route('community.invitations.accept', $community->id))
            ->assertRedirect(route('community.show', $community->id));

        $this->assertSame('approved', $this->membershipStatus($community, $invitee));
        $this->assertTrue($community->hasMember($invitee));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_invitee_can_decline(): void
    {
        [$community, $owner, $invitee] = $this->privateCommunity();
        $this->actingAs($owner)->post(route('community.invitations.store', $community->id), ['username' => 'lucky_friend']);

        $this->actingAs($invitee)->delete(route('community.leave', $community->id))->assertRedirect();

        $this->assertNull($this->membershipStatus($community, $invitee));
        $this->assertNotNull(UserNotification::where('user_id', $invitee->id)->sole()->read_at);
    }

    public function test_owner_can_cancel_an_invitation(): void
    {
        [$community, $owner, $invitee] = $this->privateCommunity();
        $this->actingAs($owner)->post(route('community.invitations.store', $community->id), ['username' => 'lucky_friend']);

        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertSee('Waiting for a reply')
            ->assertSee('Cancel invite');

        $this->actingAs($owner)->delete(route('community.members.remove', [$community->id, $invitee->id]))->assertRedirect();

        $this->assertNull($this->membershipStatus($community, $invitee));
        // 取り消された招待の通知は相手の通知欄から消える
        $this->assertNotNull(UserNotification::where('user_id', $invitee->id)->sole()->read_at);
        $this->actingAs($invitee)->patch(route('community.invitations.accept', $community->id))->assertNotFound();
    }

    public function test_only_invited_people_can_join_a_private_community(): void
    {
        [$community, , $invitee] = $this->privateCommunity();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)->post(route('community.join', $community->id))->assertForbidden();
        $this->actingAs($stranger)->patch(route('community.invitations.accept', $community->id))->assertNotFound();
        $this->actingAs($invitee)->patch(route('community.invitations.accept', $community->id))->assertNotFound();

        $this->assertSame(0, $community->memberships()->count());
    }

    public function test_only_the_owner_can_invite(): void
    {
        [$community, , $invitee] = $this->privateCommunity();
        $member = User::factory()->create();
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        $this->actingAs($member)
            ->post(route('community.invitations.store', $community->id), ['username' => 'lucky_friend'])
            ->assertForbidden();

        $this->assertNull($this->membershipStatus($community, $invitee));
        $this->actingAs($member)->get(route('community.show', $community->id))->assertDontSee('Invite members');
    }

    public function test_invalid_invitations_show_an_error_and_change_nothing(): void
    {
        [$community, $owner, $invitee] = $this->privateCommunity();
        $member = User::factory()->create(['username' => 'already_here']);
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $this->actingAs($owner)->post(route('community.invitations.store', $community->id), ['username' => 'lucky_friend']);

        foreach (['nobody_by_this_name', 'club_owner', 'already_here', 'lucky_friend', ''] as $username) {
            $this->actingAs($owner)
                ->post(route('community.invitations.store', $community->id), ['username' => $username])
                ->assertSessionHasErrorsIn('invite', 'username');
        }

        $this->assertSame(1, UserNotification::where('user_id', $invitee->id)->count());
        $this->assertSame('approved', $this->membershipStatus($community, $member));
    }

    public function test_at_sign_is_allowed_and_inviting_a_requester_approves_them(): void
    {
        [$community, $owner] = $this->privateCommunity();
        $community->update(['visibility' => 'approval']);
        $requester = User::factory()->create(['username' => 'keen_requester']);
        $this->actingAs($requester)->post(route('community.join', $community->id));
        $this->assertSame('pending', $this->membershipStatus($community, $requester));

        $this->actingAs($owner)
            ->post(route('community.invitations.store', $community->id), ['username' => '@keen_requester'])
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', $this->membershipStatus($community, $requester));
    }

    public function test_invitation_notification_opens_the_community_page(): void
    {
        [$community, $owner, $invitee] = $this->privateCommunity();
        $this->actingAs($owner)->post(route('community.invitations.store', $community->id), ['username' => 'lucky_friend']);
        $notification = UserNotification::where('user_id', $invitee->id)->sole();

        $this->actingAs($invitee)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('community.show', $community->id));
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
