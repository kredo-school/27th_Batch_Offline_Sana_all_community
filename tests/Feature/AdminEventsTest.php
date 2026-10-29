<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Jobs\NotifyEventParticipantsOnUpdateJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_event_list_and_edit_actions_render(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent(User::factory()->create());

        $this->actingAs($admin)
            ->get(route('admin.events'))
            ->assertOk()
            ->assertSee(route('admin.events.show', $event), false)
            ->assertSee(route('admin.events.edit', $event), false)
            ->assertSee(route('admin.events.destroy', $event), false)
            ->assertSee('Approval');

        $this->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertViewIs('admin.events.show')
            ->assertSee('Original event')
            ->assertSee('Original description')
            ->assertSee('Owner')
            ->assertSee('Unlimited')
            ->assertSee('Approval required')
            ->assertSee(route('admin.events.edit', $event), false);

        $this->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('Edit Event')
            ->assertSee('Require approval to join');
    }

    public function test_admin_can_update_and_soft_delete_an_event(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create();
        $event = $this->makeEvent($owner);
        $category = Category::create(['name' => 'Updated category']);

        $this->actingAs($admin)
            ->patch(route('admin.events.update', $event), [
                'title' => 'Updated event title',
                'description' => 'Updated description',
                'language' => 'ja',
                'region' => 'Kansai',
                'location' => 'Osaka',
                'start_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
                'join_till' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'end_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'capacity' => 30,
                'price' => 12.50,
                'approval_required' => '1',
                'visibility' => 'private',
                'status' => 'draft',
                'categories' => [$category->id],
            ])
            ->assertRedirect(route('admin.events.edit', $event));

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'owner_id' => $owner->id,
            'title' => 'Updated event title',
            'description' => 'Updated description',
            'language' => 'ja',
            'approval_required' => true,
            'visibility' => 'private',
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('event_categories', [
            'event_id' => $event->id,
            'category_id' => $category->id,
        ]);
        Queue::assertPushed(NotifyEventParticipantsOnUpdateJob::class, function ($job) use ($event) {
            return $job->event->is($event)
                && in_array('title', $job->changedFields, true);
        });

        $this->delete(route('admin.events.destroy', $event))
            ->assertRedirect(route('admin.events'));

        $this->assertSoftDeleted($event);
    }

    public function test_event_details_display_owner_profile_avatar_and_handle_missing_avatar(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['username' => 'event_owner']);
        $owner->profile()->create(['avatar' => 'avatars/event-owner.jpg']);
        $event = $this->makeEvent($owner);

        $this->actingAs($admin)
            ->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee(asset('storage/avatars/event-owner.jpg'), false)
            ->assertSee('event_owner');

        $owner->profile()->delete();

        $this->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee(asset('images/onlylogo.jpg'), false)
            ->assertSee('event_owner');
    }

    private function makeEvent(User $owner): Event
    {
        return Event::create([
            'owner_id' => $owner->id,
            'title' => 'Original event',
            'description' => 'Original description',
            'language' => 'en',
            'location' => 'Tokyo',
            'start_at' => now()->addDays(2),
            'join_till' => now()->addDay(),
            'end_at' => now()->addDays(3),
            'price' => 0,
            'approval_required' => false,
            'visibility' => 'public',
            'status' => 'published',
        ]);
    }
}
