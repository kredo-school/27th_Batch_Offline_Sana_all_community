<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommunitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_communities_list_shows_approved_participant_count_including_owner(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'ja',
        ]);
        $community = Community::create(['owner_id' => $admin->id, 'name' => 'Hikers', 'visibility' => 'public', 'language' => 'en']);
        foreach ([['approved', 1], ['approved', 2], ['pending', 3]] as [$status]) {
            CommunityMember::create([
                'community_id' => $community->id,
                'user_id' => User::factory()->create()->id,
                'status' => $status,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.communities'))
            ->assertOk()
            ->assertSee(__('Members'))
            ->assertViewHas('records', fn ($records) => (int) $records->first()->members_count === 3);
    }

    public function test_admin_can_view_community_details_with_members(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'ja',
        ]);
        $community = Community::create([
            'owner_id' => $admin->id,
            'name' => 'Hikers',
            'description' => 'We hike',
            'category' => 'Sports',
            'location' => 'Cebu City',
            'guidelines' => 'Be respectful.',
            'visibility' => 'approval',
            'language' => 'ja',
        ]);
        $member = User::factory()->create(['username' => 'approved_member']);
        $pending = User::factory()->create(['username' => 'pending_member']);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $member->id, 'status' => 'approved']);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $pending->id, 'status' => 'pending']);

        $this->actingAs($admin)
            ->get(route('admin.communities'))
            ->assertOk()
            ->assertSee(route('admin.communities.show', $community), false);

        $this->get(route('admin.communities.show', $community))
            ->assertOk()
            ->assertSee('Hikers')
            ->assertSee('We hike')
            ->assertSee('スポーツ')
            ->assertSee('Cebu City')
            ->assertSee('Be respectful.')
            ->assertSee('承認制')
            ->assertSee('日本語')
            ->assertSee('approved_member')
            ->assertSee('pending_member')
            ->assertViewHas('community', fn ($c) => (int) $c->members_count === 2);
    }

    public function test_admin_can_edit_and_delete_a_community(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $community = Community::create(['owner_id' => $admin->id, 'name' => 'Hikers', 'visibility' => 'public', 'language' => 'en']);

        $this->actingAs($admin)
            ->get(route('admin.communities'))
            ->assertOk()
            ->assertSee(route('admin.communities.edit', $community), false)
            ->assertSee(route('admin.communities.destroy', $community), false);

        $this->get(route('admin.communities.edit', $community))
            ->assertOk()
            ->assertSee('Hikers');

        $this->patch(route('admin.communities.update', $community), [
            'name' => 'Runners',
            'description' => 'Run together',
            'language' => 'ja',
            'visibility' => 'private',
        ])->assertRedirect(route('admin.communities'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('communities', ['id' => $community->id, 'name' => 'Runners', 'visibility' => 'private', 'language' => 'ja']);

        $this->patch(route('admin.communities.update', $community), ['name' => '', 'language' => 'en', 'visibility' => 'public'])
            ->assertSessionHasErrors('name');

        $this->delete(route('admin.communities.destroy', $community))
            ->assertRedirect(route('admin.communities'));

        $this->assertSoftDeleted($community);
    }

    public function test_admin_can_update_all_community_information_and_manage_members(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'ja',
        ]);
        $oldOwner = $admin;
        $newOwner = User::factory()->create(['name' => 'New Owner', 'username' => 'new_owner']);
        $member = User::factory()->create(['name' => 'Approved Member', 'username' => 'approved_member']);
        $pending = User::factory()->create(['name' => 'Pending Member', 'username' => 'pending_member']);
        $community = Community::create([
            'owner_id' => $oldOwner->id,
            'name' => 'Hikers',
            'description' => 'Old description',
            'category' => 'Sports',
            'location' => 'Old location',
            'guidelines' => 'Old guidelines',
            'visibility' => 'public',
            'language' => 'en',
        ]);
        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'approved',
            'joined_at' => now(),
        ]);
        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $pending->id,
            'role' => 'member',
            'status' => 'pending',
            'joined_at' => null,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.communities.update', $community), [
                'name' => 'Running Club',
                'description' => 'A complete updated description.',
                'owner_id' => $newOwner->id,
                'category' => 'Sports',
                'location' => 'Cebu City',
                'guidelines' => 'Updated community guidelines.',
                'language' => 'ja',
                'visibility' => 'approval',
            ])
            ->assertRedirect(route('admin.communities'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'owner_id' => $newOwner->id,
            'name' => 'Running Club',
            'description' => 'A complete updated description.',
            'category' => 'Sports',
            'location' => 'Cebu City',
            'guidelines' => 'Updated community guidelines.',
            'language' => 'ja',
            'visibility' => 'approval',
        ]);
        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $oldOwner->id,
            'role' => 'member',
            'status' => 'approved',
        ]);

        $this->patch(route('admin.communities.members.role', [$community, $member]), [
            'role' => 'moderator',
        ])->assertRedirect(route('admin.communities'))
            ->assertSessionHas('status');
        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'role' => 'moderator',
            'status' => 'approved',
        ]);

        $this->delete(route('admin.communities.members.remove', [$community, $pending]))
            ->assertRedirect(route('admin.communities'))
            ->assertSessionHas('status');
        $this->assertDatabaseMissing('community_members', [
            'community_id' => $community->id,
            'user_id' => $pending->id,
        ]);

        $this->get(route('admin.communities.show', $community))
            ->assertOk()
            ->assertSee('Running Club')
            ->assertSee('A complete updated description.')
            ->assertSee('Cebu City')
            ->assertSee('Updated community guidelines.')
            ->assertSee('approved_member')
            ->assertSee('モデレーター');
    }

    public function test_admin_can_remove_organizer_and_choose_a_new_one(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $oldOwner = User::factory()->create(['username' => 'old_owner']);
        $newOwner = User::factory()->create(['username' => 'new_owner']);
        $outsider = User::factory()->create(['username' => 'outsider']);
        $community = Community::create([
            'owner_id' => $oldOwner->id,
            'name' => 'Hikers',
            'visibility' => 'public',
            'language' => 'en',
        ]);
        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $newOwner->id,
            'role' => 'member',
            'status' => 'approved',
            'joined_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.communities.edit', $community))
            ->assertOk()
            ->assertSee(route('admin.communities.owner.replace', $community), false);

        $this->patch(route('admin.communities.owner.replace', $community), [
            'new_owner_id' => $outsider->id,
        ])->assertNotFound();

        $this->patch(route('admin.communities.owner.replace', $community), [
            'new_owner_id' => $oldOwner->id,
        ])->assertStatus(422);

        $this->patch(route('admin.communities.owner.replace', $community), [
            'new_owner_id' => $newOwner->id,
        ])->assertRedirect(route('admin.communities'))
            ->assertSessionHas('status');

        $this->assertSame($newOwner->id, $community->fresh()->owner_id);
        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $oldOwner->id,
            'role' => 'member',
            'status' => 'approved',
        ]);
        $this->assertDatabaseMissing('community_members', [
            'community_id' => $community->id,
            'user_id' => $newOwner->id,
        ]);
    }
}
