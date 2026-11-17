<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPrivacyAndRequestResultTest extends TestCase
{
    use RefreshDatabase;

    private function attach(Community $community, User $user, string $status): void
    {
        $community->members()->attach($user->id, [
            'role' => 'member', 'status' => $status, 'joined_at' => $status === 'approved' ? now() : null,
        ]);
    }

    // ---- 招待のみのコミュニティの見え方 ----

    public function test_private_community_is_hidden_from_people_without_a_connection(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $secret = $owner->communitiesOwned()->create(['name' => 'Hidden Society', 'description' => 'Shh.', 'visibility' => 'private']);
        $owner->communitiesOwned()->create(['name' => 'Open Society', 'description' => 'Hi.', 'visibility' => 'public']);
        $secret->events()->create([
            'title' => 'Secret Picnic', 'owner_id' => $owner->id,
            'start_at' => now()->addWeek(), 'end_at' => now()->addWeek()->addHour(), 'join_till' => now()->addWeek(),
        ]);

        $this->actingAs($stranger)
            ->get(route('community.index'))
            ->assertOk()
            ->assertSee('Open Society')
            ->assertDontSee('Hidden Society')
            ->assertDontSee('Secret Picnic');

        $this->actingAs($stranger)->get(route('community.show', $secret->id))->assertNotFound();
        $this->actingAs($stranger)->get(route('community.index', ['search' => 'Hidden']))->assertDontSee('Hidden Society');
    }

    public function test_owner_members_and_invited_people_can_see_a_private_community(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $invited = User::factory()->create();
        $secret = $owner->communitiesOwned()->create(['name' => 'Hidden Society', 'description' => 'Shh.', 'visibility' => 'private']);
        $this->attach($secret, $member, 'approved');
        $this->attach($secret, $invited, 'invited');

        foreach ([$owner, $member, $invited] as $user) {
            $this->actingAs($user)->get(route('community.index'))->assertSee('Hidden Society');
            $this->actingAs($user)->get(route('community.show', $secret->id))->assertOk();
        }
    }

    public function test_declining_an_invitation_returns_to_the_community_list(): void
    {
        $owner = User::factory()->create();
        $invited = User::factory()->create();
        $secret = $owner->communitiesOwned()->create(['name' => 'Hidden Society', 'description' => 'Shh.', 'visibility' => 'private']);
        $this->attach($secret, $invited, 'invited');

        $this->actingAs($invited)
            ->delete(route('community.leave', $secret->id))
            ->assertRedirect(route('community.index'));
        $this->actingAs($invited)->get(route('community.show', $secret->id))->assertNotFound();
    }

    public function test_cancelled_invitation_notification_no_longer_opens_the_hidden_community(): void
    {
        $owner = User::factory()->create();
        $invited = User::factory()->create(['username' => 'maybe_friend']);
        $secret = $owner->communitiesOwned()->create(['name' => 'Hidden Society', 'description' => 'Shh.', 'visibility' => 'private']);
        $this->actingAs($owner)->post(route('community.invitations.store', $secret->id), ['username' => 'maybe_friend']);
        $this->actingAs($owner)->delete(route('community.members.remove', [$secret->id, $invited->id]));

        $notification = UserNotification::where('user_id', $invited->id)->sole();
        $this->actingAs($invited)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('notifications.index', ['type' => 'community']));
    }

    // ---- 参加申請の結果の通知 ----

    private function approvalCommunityWithRequest(): array
    {
        $owner = User::factory()->create(['username' => 'club_owner']);
        $requester = User::factory()->create(['username' => 'hopeful_one']);
        $community = $owner->communitiesOwned()->create(['name' => 'Approval Club', 'description' => 'Ask.', 'visibility' => 'approval']);
        $this->actingAs($requester)->post(route('community.join', $community->id));

        return [$community, $owner, $requester];
    }

    public function test_approving_a_request_notifies_the_requester(): void
    {
        [$community, $owner, $requester] = $this->approvalCommunityWithRequest();

        $this->actingAs($owner)->patch(route('community.members.approve', [$community->id, $requester->id]))->assertRedirect();

        $notification = UserNotification::where('user_id', $requester->id)->sole();
        $this->assertSame('community_join_approved', $notification->type);
        $this->assertSame('club_owner', $notification->sender_name);

        // オーナー側の参加申請の通知は片付く
        $this->assertSame(0, UserNotification::where('user_id', $owner->id)->whereNull('read_at')->count());

        $this->actingAs($requester)
            ->get(route('profile.show', $requester->id))
            ->assertSeeInOrder(['Approval Club', 'Your join request was approved']);
        $this->actingAs($requester)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('community.show', $community->id));
    }

    public function test_declining_a_request_notifies_the_requester(): void
    {
        [$community, $owner, $requester] = $this->approvalCommunityWithRequest();

        $this->actingAs($owner)->delete(route('community.members.remove', [$community->id, $requester->id]))->assertRedirect();

        $notification = UserNotification::where('user_id', $requester->id)->sole();
        $this->assertSame('community_join_declined', $notification->type);
        $this->assertSame(0, UserNotification::where('user_id', $owner->id)->whereNull('read_at')->count());
        $this->actingAs($requester)
            ->get(route('profile.show', $requester->id))
            ->assertSee('Your join request was declined');
    }

    public function test_removing_a_member_or_cancelling_an_invite_is_not_reported_as_a_declined_request(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $invited = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Approval Club', 'description' => 'Ask.', 'visibility' => 'approval']);
        $this->attach($community, $member, 'approved');
        $this->attach($community, $invited, 'invited');

        $this->actingAs($owner)->delete(route('community.members.remove', [$community->id, $member->id]));
        $this->actingAs($owner)->delete(route('community.members.remove', [$community->id, $invited->id]));

        $this->assertSame(0, UserNotification::whereIn('type', ['community_join_approved', 'community_join_declined'])->count());
    }

    public function test_inviting_a_requester_also_tells_them_they_were_approved(): void
    {
        [$community, $owner, $requester] = $this->approvalCommunityWithRequest();

        $this->actingAs($owner)->post(route('community.invitations.store', $community->id), ['username' => 'hopeful_one']);

        $this->assertSame('community_join_approved', UserNotification::where('user_id', $requester->id)->sole()->type);
    }
}
