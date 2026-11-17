<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrivateCommunityEventTest extends TestCase
{
    use RefreshDatabase;

    private function eventIn(?Community $community, User $owner, string $title): Event
    {
        $event = Event::create([
            'title' => $title,
            'owner_id' => $owner->id,
            'community_id' => $community?->id,
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHours(2),
            'join_till' => now()->addDays(6),
        ]);
        // 詳細ページの閲覧記録にカテゴリーが必要（作成フォームでも必須）
        $event->categories()->attach(Category::create(['name' => 'Sample ' . Str::random(6)])->id);

        return $event;
    }

    private function setUpCommunities(): array
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $invited = User::factory()->create();
        $stranger = User::factory()->create();
        $secret = $owner->communitiesOwned()->create(['name' => 'Hidden Society', 'description' => 'Shh.', 'visibility' => 'private']);
        $open = $owner->communitiesOwned()->create(['name' => 'Open Society', 'description' => 'Hi.', 'visibility' => 'public']);
        $secret->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $secret->members()->attach($invited->id, ['role' => 'member', 'status' => 'invited']);

        return [
            'owner' => $owner, 'member' => $member, 'invited' => $invited, 'stranger' => $stranger,
            'secretEvent' => $this->eventIn($secret, $owner, 'Secret Picnic'),
            'openEvent' => $this->eventIn($open, $owner, 'Open Picnic'),
            'plainEvent' => $this->eventIn(null, $owner, 'City Marathon'),
        ];
    }

    public function test_strangers_cannot_open_or_act_on_events_of_a_private_community(): void
    {
        ['stranger' => $stranger, 'secretEvent' => $event] = $this->setUpCommunities();

        $this->actingAs($stranger)->get(route('events.show', $event->id))->assertNotFound();
        $this->actingAs($stranger)->post(route('events.join', $event->id))->assertNotFound();
        $this->actingAs($stranger)->post(route('events.comments.store', $event->id), ['content' => 'Hi'])->assertNotFound();
        $this->actingAs($stranger)->post(route('bookmarks.store', $event->id))->assertNotFound();

        $this->assertSame(0, $event->participants()->count());
        $this->assertSame(0, $event->bookmarkedBy()->count());
    }

    public function test_owner_members_and_invited_people_can_open_events_of_a_private_community(): void
    {
        $data = $this->setUpCommunities();

        foreach (['owner', 'member', 'invited'] as $who) {
            $this->actingAs($data[$who])
                ->get(route('events.show', $data['secretEvent']->id))
                ->assertOk()
                ->assertSee('Secret Picnic');
        }
    }

    public function test_events_of_public_communities_and_outside_communities_stay_open(): void
    {
        ['stranger' => $stranger, 'openEvent' => $openEvent, 'plainEvent' => $plainEvent] = $this->setUpCommunities();

        $this->actingAs($stranger)->get(route('events.show', $openEvent->id))->assertOk();
        $this->actingAs($stranger)->get(route('events.show', $plainEvent->id))->assertOk();
    }

    public function test_hosts_profile_hides_private_community_events_from_strangers_only(): void
    {
        ['owner' => $owner, 'member' => $member, 'stranger' => $stranger] = $this->setUpCommunities();

        $this->actingAs($stranger)
            ->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSee('Open Picnic')
            ->assertSee('City Marathon')
            ->assertDontSee('Secret Picnic');

        $this->actingAs($member)->get(route('profile.show', $owner->id))->assertSee('Secret Picnic');
        $this->actingAs($owner)->get(route('profile.show', $owner->id))->assertSee('Secret Picnic');
    }

    public function test_bookmarks_of_events_no_longer_visible_are_hidden(): void
    {
        ['member' => $member, 'secretEvent' => $event] = $this->setUpCommunities();
        $this->actingAs($member)->post(route('bookmarks.store', $event->id))->assertRedirect();

        // コミュニティを抜けたら、ブックマーク一覧にも出ない
        $event->community->memberships()->where('user_id', $member->id)->delete();

        $this->actingAs($member)
            ->get(route('profile.show', $member->id))
            ->assertDontSee('Secret Picnic');
    }

    public function test_cancel_notification_for_an_event_no_longer_visible_opens_the_notification_list(): void
    {
        ['owner' => $owner, 'member' => $member, 'secretEvent' => $event] = $this->setUpCommunities();
        $event->users()->attach($member->id, ['status' => 'approved']);
        $this->actingAs($owner)->patch(route('events.cancel', $event), ['cancel_reason' => 'Storm']);
        $notification = UserNotification::where('user_id', $member->id)->where('type', 'event_cancelled')->sole();

        $event->community->memberships()->where('user_id', $member->id)->delete();

        $this->actingAs($member)
            ->get(route('notifications.show', $notification))
            ->assertRedirect(route('notifications.index', ['type' => 'community']));
    }
}
