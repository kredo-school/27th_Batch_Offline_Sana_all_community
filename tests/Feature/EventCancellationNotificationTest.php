<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCancellationNotificationTest extends TestCase
{
    use RefreshDatabase;

    // inCommunity が true ならコミュニティ内のイベント、false なら通常のイベントを作る
    private function eventWithParticipants(bool $inCommunity = true): array
    {
        $owner = User::factory()->create(['username' => 'host_user']);
        $approved = User::factory()->create();
        $pending = User::factory()->create();
        $rejected = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Beach Club', 'description' => 'Cleanups.']);
        $event = Event::create([
            'title' => 'Beach Cleanup',
            'owner_id' => $owner->id,
            'community_id' => $inCommunity ? $community->id : null,
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(2),
            'join_till' => now()->addDays(6),
        ]);
        $event->categories()->attach(Category::create(['name' => 'Outdoors'])->id);
        $event->users()->attach($approved->id, ['status' => 'approved']);
        $event->users()->attach($pending->id, ['status' => 'pending']);
        $event->users()->attach($rejected->id, ['status' => 'rejected']);

        return [$event, $owner, $approved, $pending, $rejected];
    }

    public function test_cancelling_notifies_approved_and_pending_participants_with_the_reason(): void
    {
        [$event, $owner, $approved, $pending, $rejected] = $this->eventWithParticipants();

        $this->actingAs($owner)
            ->patch(route('events.cancel', $event), ['cancel_reason' => 'Storm warning'])
            ->assertRedirect(route('events.show', $event));

        foreach ([$approved, $pending] as $user) {
            $notification = UserNotification::where('user_id', $user->id)->sole();
            $this->assertSame('event_cancelled', $notification->type);
            $this->assertSame('host_user', $notification->sender_name);
            $this->assertStringContainsString('Beach Cleanup', $notification->message);
            $this->assertStringContainsString('Storm warning', $notification->message);
            $this->assertTrue($notification->related->is($event));
        }

        // 却下された人と主催者本人には送らない
        $this->assertSame(0, UserNotification::whereIn('user_id', [$rejected->id, $owner->id])->count());
    }

    public function test_cancel_notification_is_listed_with_community_notifications_not_the_bell(): void
    {
        [$event, $owner, $approved] = $this->eventWithParticipants();
        $this->actingAs($owner)->patch(route('events.cancel', $event), ['cancel_reason' => 'Storm warning']);

        // ヘッダーのコミュニティ通知欄に、コミュニティ名・イベント名・理由が出る
        $this->actingAs($approved)
            ->get(route('profile.show', $approved->id))
            ->assertOk()
            ->assertSeeInOrder(['Beach Club', 'Event cancelled: Beach Cleanup', 'Reason: Storm warning'])
            ->assertDontSee('<span class="d-block">Event cancelled</span>', false);

        // 通知一覧のコミュニティの絞り込みにも含まれ、一般の絞り込みには含まれない
        $this->actingAs($approved)
            ->get(route('notifications.index', ['type' => 'community']))
            ->assertViewHas('records', fn ($records) => $records->pluck('type')->contains('event_cancelled'));
        $this->actingAs($approved)
            ->get(route('notifications.index', ['type' => 'general']))
            ->assertViewHas('records', fn ($records) => ! $records->pluck('type')->contains('event_cancelled'));
    }

    public function test_cancelling_an_event_outside_communities_sends_no_notifications(): void
    {
        [$event, $owner] = $this->eventWithParticipants(inCommunity: false);

        $this->actingAs($owner)
            ->patch(route('events.cancel', $event), ['cancel_reason' => 'Storm warning'])
            ->assertRedirect(route('events.show', $event));

        // 中止そのものは今までどおりできる
        $this->assertSame('cancelled', $event->fresh()->status);
        $this->assertSame(0, UserNotification::count());
    }

    public function test_no_notifications_when_the_cancel_is_refused(): void
    {
        [$event, $owner, $approved] = $this->eventWithParticipants();

        // 主催者以外は中止できない
        $this->actingAs($approved)
            ->patch(route('events.cancel', $event), ['cancel_reason' => 'Not mine'])
            ->assertForbidden();

        // 理由がないと中止できない
        $this->actingAs($owner)
            ->patch(route('events.cancel', $event), ['cancel_reason' => ''])
            ->assertSessionHasErrors('cancel_reason');

        $this->assertSame(0, UserNotification::count());
        $this->assertNotSame('cancelled', $event->fresh()->status);
    }

    public function test_cancelling_twice_does_not_send_duplicate_notifications(): void
    {
        [$event, $owner] = $this->eventWithParticipants();

        $this->actingAs($owner)->patch(route('events.cancel', $event), ['cancel_reason' => 'Storm warning']);
        $this->actingAs($owner)->patch(route('events.cancel', $event), ['cancel_reason' => 'Again']);

        $this->assertSame(2, UserNotification::count());
    }

    public function test_opening_the_notification_marks_it_read_and_shows_the_event(): void
    {
        [$event, $owner, $approved] = $this->eventWithParticipants();
        $this->actingAs($owner)->patch(route('events.cancel', $event), ['cancel_reason' => 'Storm warning']);
        $notification = UserNotification::where('user_id', $approved->id)->sole();

        $this->actingAs($approved)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('events.show', $event->id));

        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($approved)
            ->get(route('events.show', $event->id))
            ->assertOk()
            ->assertSee('This event has been cancelled.')
            ->assertSee('Storm warning');
    }
}
