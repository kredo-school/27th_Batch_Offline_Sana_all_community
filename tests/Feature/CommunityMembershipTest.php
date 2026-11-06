<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityMembershipTest extends TestCase
{
    use RefreshDatabase;

    private function community(string $visibility = 'public'): array
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create([
            'name' => 'Join Club',
            'description' => 'Join us.',
            'visibility' => $visibility,
        ]);

        return [$community, $owner];
    }

    private function membershipStatus(Community $community, User $user): ?string
    {
        return $community->memberships()->where('user_id', $user->id)->value('status');
    }

    public function test_user_joins_public_community_immediately_and_can_chat(): void
    {
        [$community] = $this->community('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('community.join', $community->id))
            ->assertRedirect(route('community.show', $community->id));

        $this->assertSame('approved', $this->membershipStatus($community, $user));
        $this->actingAs($user)
            ->postJson(route('community.chat.store', $community->id), ['message' => 'Hi!'])
            ->assertCreated();
    }

    public function test_approval_community_creates_pending_request_that_owner_can_approve(): void
    {
        [$community, $owner] = $this->community('approval');
        $user = User::factory()->create(['name' => 'Waiting Person']);

        $this->actingAs($user)->post(route('community.join', $community->id));
        $this->assertSame('pending', $this->membershipStatus($community, $user));

        $this->actingAs($user)
            ->get(route('community.show', $community->id))
            ->assertSee('Request pending');

        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertSee('Join requests')
            ->assertSee(route('community.members.approve', [$community->id, $user->id]), false);

        $this->actingAs($owner)
            ->patch(route('community.members.approve', [$community->id, $user->id]))
            ->assertRedirect(route('community.show', $community->id) . '#members');

        $this->assertSame('approved', $this->membershipStatus($community, $user));
    }

    public function test_owner_can_decline_request_and_remove_member(): void
    {
        [$community, $owner] = $this->community('approval');
        $pending = User::factory()->create();
        $member = User::factory()->create();
        $community->members()->attach($pending->id, ['role' => 'member', 'status' => 'pending']);
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        $this->actingAs($owner)->delete(route('community.members.remove', [$community->id, $pending->id]));
        $this->actingAs($owner)->delete(route('community.members.remove', [$community->id, $member->id]));

        $this->assertNull($this->membershipStatus($community, $pending));
        $this->assertNull($this->membershipStatus($community, $member));
    }

    public function test_non_owner_cannot_approve_or_remove(): void
    {
        [$community] = $this->community('approval');
        $pending = User::factory()->create();
        $stranger = User::factory()->create();
        $community->members()->attach($pending->id, ['role' => 'member', 'status' => 'pending']);

        $this->actingAs($stranger)
            ->patch(route('community.members.approve', [$community->id, $pending->id]))
            ->assertForbidden();
        $this->actingAs($stranger)
            ->delete(route('community.members.remove', [$community->id, $pending->id]))
            ->assertForbidden();

        $this->assertSame('pending', $this->membershipStatus($community, $pending));
    }

    public function test_private_community_cannot_be_joined(): void
    {
        [$community] = $this->community('private');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('community.show', $community->id))
            ->assertSee('Invite only')
            ->assertDontSee(route('community.join', $community->id), false);

        $this->actingAs($user)
            ->post(route('community.join', $community->id))
            ->assertForbidden();

        $this->assertNull($this->membershipStatus($community, $user));
    }

    public function test_member_can_leave(): void
    {
        [$community] = $this->community('public');
        $user = User::factory()->create();
        $community->members()->attach($user->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        $this->actingAs($user)
            ->get(route('community.show', $community->id))
            ->assertSee('Joined')
            ->assertSee(route('community.leave', $community->id), false);

        $this->actingAs($user)->delete(route('community.leave', $community->id));

        $this->assertNull($this->membershipStatus($community, $user));
    }
}
