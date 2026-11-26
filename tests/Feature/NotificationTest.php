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

    public function test_notification_list_keeps_row_navigation_in_japanese(): void
    {
        $user = User::factory()->create(['preferred_language' => 'ja']);
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'general',
            'title' => 'Japanese language test',
            'message' => 'Notification detail',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertViewHas('title', 'Notifications')
            ->assertSee('通知')
            ->assertSee(route('notifications.show', $notification), false);
    }

    public function test_admin_notification_list_keeps_icons_and_row_navigation_in_japanese(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'ja',
        ]);
        $recipient = User::factory()->create();
        $notification = UserNotification::create([
            'user_id' => $recipient->id,
            'sender_name' => 'Admin',
            'type' => 'general',
            'title' => 'Admin Japanese notification',
            'message' => 'Details',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.notifications'))
            ->assertOk()
            ->assertViewHas('title', 'Notifications')
            ->assertSee('通知')
            ->assertSee(route('admin.notifications.show', $notification), false)
            ->assertSee(route('admin.notifications.edit', $notification), false)
            ->assertSee('fa-solid fa-pen', false)
            ->assertSee('fa-solid fa-trash', false);
    }

    public function test_admin_community_filter_includes_types_containing_community(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $recipient = User::factory()->create();
        UserNotification::create([
            'user_id' => $recipient->id,
            'type' => 'community_custom_update',
            'title' => 'Future community notification',
        ]);
        UserNotification::create([
            'user_id' => $recipient->id,
            'type' => 'general',
            'title' => 'General notification',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.notifications', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('records', function ($records): bool {
                return $records->count() === 1
                    && $records->first()->title === 'Future community notification';
            });
    }

    public function test_community_message_is_excluded_from_user_and_admin_notification_lists(): void
    {
        $user = User::factory()->create();
        $community = Community::create([
            'owner_id' => $user->id,
            'name' => 'Chat Club',
            'description' => 'Talk.',
        ]);
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'community_message',
            'title' => 'Should not appear in notifications',
            'related_type' => Community::class,
            'related_id' => $community->id,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertDontSee('Should not appear in notifications');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.notifications'))
            ->assertOk()
            ->assertDontSee('Should not appear in notifications');
    }

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
            ->assertRedirect(route('community.show', $community->id).'#chat');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_community_approval_notification_opens_the_community_directly(): void
    {
        foreach (['community_join_approved', 'community_approval'] as $type) {
            $user = User::factory()->create();
            $community = Community::create([
                'owner_id' => User::factory()->create()->id,
                'name' => 'Approval Club',
                'description' => 'Approved.',
                'visibility' => 'approval',
            ]);
            $notification = UserNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => 'Community join approved',
                'related_type' => Community::class,
                'related_id' => $community->id,
            ]);

            $this->actingAs($user)
                ->get(route('notifications.show', $notification))
                ->assertRedirect(route('community.show', $community->id));
        }
    }

    public function test_read_notifications_are_removed_from_the_dropdown_menu(): void
    {
        $user = User::factory()->create();
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'general',
            'title' => 'Unread bell notice',
            'message' => 'This one should still show.',
        ]);
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'general',
            'title' => 'Already read notice',
            'message' => 'This one should disappear after reading.',
            'read_at' => now()->subHour(),
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Unread bell notice')
            ->assertDontSee('Already read notice');
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

    public function test_help_application_result_notifications_open_the_help_page(): void
    {
        foreach (['help_application_accepted', 'help_application_rejected'] as $type) {
            $user = User::factory()->create();
            $notification = UserNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => 'Help application result',
                'related_id' => 123,
            ]);

            $this->actingAs($user)
                ->get(route('notifications.show', $notification))
                ->assertRedirect(route('helps.show', 123));
        }
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
            'type' => 'community_custom_update',
            'title' => 'Future community notice',
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
                    && ! $records->contains('title', 'Community notice')
                    && $records->contains('title', 'Future community notice');
            });
    }

    public function test_community_filter_includes_notifications_related_to_communities(): void
    {
        $user = User::factory()->create();
        $community = Community::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Related Club',
            'description' => 'Community notices.',
        ]);
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'general',
            'title' => 'Community-related notice',
            'related_type' => Community::class,
            'related_id' => $community->id,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('records', fn ($records) => $records->count() === 1
                && $records->first()->title === 'Community-related notice');
    }

    public function test_community_chat_notifications_are_listed_and_open_the_chat(): void
    {
        $user = User::factory()->create();
        $community = Community::create([
            'owner_id' => $user->id,
            'name' => 'Chat Notices',
            'description' => 'New messages.',
        ]);
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'community_message',
            'title' => 'New community chat message',
            'message' => 'There is a new message.',
            'related_type' => Community::class,
            'related_id' => $community->id,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('records', fn ($records) => $records->contains('id', $notification->id))
            ->assertSee('New community chat message')
            ->assertSee(route('community.show', $community->id).'#chat', false);

        $this->get(route('notifications.show', $notification))
            ->assertRedirect(route('community.show', $community->id).'#chat');
    }

    public function test_community_event_notifications_open_the_related_event_pages(): void
    {
        $owner = User::factory()->create();
        $community = Community::create([
            'owner_id' => $owner->id,
            'name' => 'Event Notices',
            'description' => 'Community events.',
        ]);
        $event = Event::create([
            'owner_id' => $owner->id,
            'community_id' => $community->id,
            'title' => 'Community Event',
            'description' => 'An event.',
            'start_at' => now()->addDay(),
            'join_till' => now()->addHours(12),
            'end_at' => now()->addDays(2),
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $updated = UserNotification::create([
            'user_id' => $owner->id,
            'type' => 'event_updated',
            'title' => 'Event updated',
            'related_type' => Event::class,
            'related_id' => $event->id,
        ]);
        $participantCancelled = UserNotification::create([
            'user_id' => $owner->id,
            'type' => 'participant_cancelled',
            'title' => 'Participant cancelled',
            'related_type' => Event::class,
            'related_id' => $event->id,
        ]);

        $this->actingAs($owner)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('records', fn ($records) => $records->count() === 2);

        $this->get(route('notifications.show', $updated))
            ->assertRedirect(route('events.show', $event));
        $this->get(route('notifications.show', $participantCancelled))
            ->assertRedirect(route('events.participants', $event));
    }

    public function test_community_filter_stays_on_notifications_and_shows_related_notices(): void
    {
        $user = User::factory()->create();
        $community = Community::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Related Club',
            'description' => 'Community notices.',
        ]);
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'general',
            'title' => 'Community-related notice',
            'related_type' => Community::class,
            'related_id' => $community->id,
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('title', 'Community')
            ->assertViewHas('records', fn ($records) => $records->count() === 1)
            ->assertSee('View All')
            ->assertSee('Communities')
            ->assertSee('Community-related notice')
            ->assertSee(route('notifications.show', $notification), false)
            ->assertDontSee('href="'.route('notifications.index').'" class="btn btn-white text-danger', false)
            ->assertDontSee('href="'.route('notifications.index', ['type' => 'general']).'"', false)
            ->assertDontSee('href="'.route('notifications.index', ['type' => 'maintenance']).'"', false)
            ->assertDontSee('href="'.route('notifications.index', ['type' => 'community']).'" class="btn btn-white text-success border-success', false)
            ->assertDontSee('Owned Communities')
            ->assertDontSee('Joined Communities');
    }

    public function test_community_filter_includes_notifications_for_soft_deleted_community_events(): void
    {
        $user = User::factory()->create();
        $community = Community::create([
            'owner_id' => $user->id,
            'name' => 'Archived Event Club',
            'description' => 'Community event notices.',
        ]);
        $event = Event::create([
            'owner_id' => $user->id,
            'community_id' => $community->id,
            'title' => 'Archived Community Event',
            'description' => 'An event.',
            'start_at' => now()->addDay(),
            'join_till' => now()->addHours(12),
            'end_at' => now()->addDays(2),
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'event_cancelled',
            'title' => 'Event cancelled',
            'related_type' => Event::class,
            'related_id' => $event->id,
        ]);
        $event->delete();

        $this->actingAs($user)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertOk()
            ->assertViewHas('records', fn ($records) => $records->contains('id', $notification->id));
    }
}