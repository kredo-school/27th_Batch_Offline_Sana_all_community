<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_message_notification_opens_the_community_chat(): void
    {
        $user = User::factory()->create();
        $community = Community::create([
            'owner_id' => $user->id,
            'name' => 'Chat Club',
            'description' => 'Talks.',
        ]);
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'community_message',
            'title' => 'New community message',
            'related_type' => Community::class,
            'related_id' => $community->id,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('notifications.index', [
                'type' => 'community',
                'community' => $community->id,
            ]));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_event_join_request_notification_opens_the_participants_page(): void
    {
        $owner = User::factory()->create();
        $event = Event::create([
            'owner_id' => $owner->id,
            'title' => 'Approval Event',
            'description' => 'Event description',
            'start_at' => now()->addDay(),
            'join_till' => now()->addHours(12),
            'end_at' => now()->addDays(2),
            'approval_required' => true,
            'status' => 'published',
        ]);
        $notification = UserNotification::create([
            'user_id' => $owner->id,
            'type' => 'event_join_request',
            'title' => 'Event registration request',
            'related_type' => Event::class,
            'related_id' => $event->id,
        ]);

        $this->actingAs($owner)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('events.participants', $event));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_detail_page_displays_general_notifications(): void
    {
        $user = User::factory()->create();
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'event_join_approved',
            'title' => 'Event registration approved',
            'message' => 'Your registration was approved.',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.show', $notification))
            ->assertOk()
            ->assertSee('Event registration approved')
            ->assertSee('Your registration was approved.');
    }

    public function test_community_notification_filter_excludes_other_notifications(): void
    {
        $user = User::factory()->create();
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'community_message',
            'title' => 'Community notice',
        ]);
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'general',
            'title' => 'General notice',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('records', function ($records): bool {
                return $records->count() === 1
                    && $records->first()->title === 'Community notice';
            });
    }

    public function test_community_view_provides_owned_and_joined_chat_summaries(): void
    {
        $user = User::factory()->create();
        $joinedOwner = User::factory()->create();
        $owned = Community::create(['owner_id' => $user->id, 'name' => 'Owned Club', 'description' => 'Talks.']);
        $joined = Community::create(['owner_id' => $joinedOwner->id, 'name' => 'Joined Club', 'description' => 'Talks.']);
        $joined->members()->attach($user->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $owned->messages()->create(['user_id' => $user->id, 'message' => 'One']);
        $owned->messages()->create(['user_id' => $user->id, 'message' => 'Two']);
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'community_message',
            'title' => 'New community message',
            'related_type' => Community::class,
            'related_id' => $owned->id,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('ownedCommunities', function ($communities) use ($owned): bool {
                return $communities->firstWhere('id', $owned->id)->total_chat_messages === 2
                    && $communities->firstWhere('id', $owned->id)->unread_chat_messages === 1;
            })
            ->assertViewHas('joinedCommunities', function ($communities) use ($joined): bool {
                return $communities->firstWhere('id', $joined->id) !== null;
            });
    }
}