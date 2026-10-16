<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Review;
use App\Models\Notification;
use App\Models\Community;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_view_renders_with_the_app_layout(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('home')
            ->assertSee('Sana All Community');
    }

    public function test_login_redirects_to_the_home_view(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);

        $this->get('/home')
            ->assertOk()
            ->assertViewIs('home')
            ->assertSee('Sana All Community');
    }

    public function test_registration_saves_username_and_preferred_language(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="username"', false)
            ->assertSee('name="preferred_language"', false);

        $this->post(route('register'), [
            'name' => 'Test Member',
            'username' => 'test_member_2026',
            'preferred_language' => 'ja',
            'email' => 'test-member-2026@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/home');

        $this->assertDatabaseHas('users', [
            'username' => 'test_member_2026',
            'preferred_language' => 'ja',
        ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_admin_can_manage_users_and_change_roles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertViewIs('admin.admin')
            ->assertSee('Make admin');

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertViewIs('admin.sections.index')
            ->assertSee('Categories')
            ->assertSee('aria-current="page"', false);

        foreach ([
            'admin.events',
            'admin.communities',
            'admin.notifications',
            'admin.comments',
            'admin.reviews',
        ] as $sectionRoute) {
            $this->get(route($sectionRoute))
                ->assertOk()
                ->assertViewIs('admin.sections.index')
                ->assertSee('aria-current="page"', false);
        }

        $this->post(route('admin.users.role', $user->id), [
            '_method' => 'PATCH',
            'role' => 'admin',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_activate_a_deactivated_user_from_the_modal_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $user->delete();

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('activate-user-' . $user->id, false)
            ->assertSee(route('admin.users.activate', $user->id), false);

        $this->post(route('admin.users.activate', $user->id), [
            '_method' => 'PATCH',
        ])
            ->assertRedirect();

        $this->assertNotSoftDeleted($user);
    }

    public function test_admin_can_create_edit_and_delete_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee(route('admin.categories.create'), false);

        $this->post(route('admin.categories.store'), [
            'name' => 'Community Events',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::where('name', 'Community Events')->firstOrFail();

        $this->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('Community Events');

        $this->patch(route('admin.categories.update', $category), [
            'name' => 'Local Events',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Local Events',
        ]);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSoftDeleted($category);
    }

    public function test_event_and_community_admin_lists_are_separate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $eventsResponse = $this->get(route('admin.events'));
        $eventsResponse->assertOk();
        $eventColumns = $eventsResponse->viewData('columns');

        $communitiesResponse = $this->get(route('admin.communities'));
        $communitiesResponse->assertOk();
        $communityColumns = $communitiesResponse->viewData('columns');

        $this->assertArrayHasKey('Starts', $eventColumns);
        $this->assertArrayNotHasKey('Community', $eventColumns);
        $this->assertArrayHasKey('Visibility', $communityColumns);
        $this->assertArrayNotHasKey('Starts', $communityColumns);
    }
}
