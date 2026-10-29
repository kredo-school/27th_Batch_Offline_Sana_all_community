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

    public function test_admin_communities_list_shows_approved_member_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
            ->assertSee('Members')
            ->assertViewHas('records', fn ($records) => $records->first()->members_count === 2);
    }

    public function test_admin_can_view_community_details_with_members(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $community = Community::create(['owner_id' => $admin->id, 'name' => 'Hikers', 'description' => 'We hike', 'visibility' => 'public', 'language' => 'en']);
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
            ->assertSee('approved_member')
            ->assertSee('pending_member')
            ->assertViewHas('community', fn ($c) => $c->members_count === 1);
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
        ])->assertRedirect(route('admin.communities.edit', $community));

        $this->assertDatabaseHas('communities', ['id' => $community->id, 'name' => 'Runners', 'visibility' => 'private', 'language' => 'ja']);

        $this->patch(route('admin.communities.update', $community), ['name' => '', 'language' => 'en', 'visibility' => 'public'])
            ->assertSessionHasErrors('name');

        $this->delete(route('admin.communities.destroy', $community))
            ->assertRedirect(route('admin.communities'));

        $this->assertSoftDeleted($community);
    }
}
