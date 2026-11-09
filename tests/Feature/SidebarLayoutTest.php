<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SidebarLayoutTest extends TestCase
{
    use RefreshDatabase;

    // 作成フォームではカテゴリーが必須なので、直接作るテスト用のイベントにも付けておく
    private function withCategory(Event $event): Event
    {
        $event->categories()->attach(Category::create(['name' => 'Sample ' . Str::random(6)])->id);

        return $event;
    }

    private function eventDates(): array
    {
        return [
            'start_at' => now()->addDays(2),
            'join_till' => now()->addDay(),
            'end_at' => now()->addDays(3),
        ];
    }

    public function test_community_pages_show_the_community_sidebar(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Club', 'description' => 'Talks.']);

        foreach ([
            route('community.index'),
            route('community.create'),
            route('community.show', $community->id),
            route('community.events.create', $community->id),
        ] as $url) {
            $this->actingAs($owner)->get($url)
                ->assertOk()
                ->assertSee('event-sidebar-col', false)
                ->assertSee('community-sidebar', false);
        }
    }

    public function test_events_pages_show_the_events_sidebar(): void
    {
        $owner = User::factory()->create();
        $event = $this->withCategory(Event::create(['title' => 'Open Marathon', 'owner_id' => $owner->id, ...$this->eventDates()]));

        foreach ([
            route('events.index'),
            route('events.create'),
            route('events.show', $event->id),
            route('events.edit', $event->id),
        ] as $url) {
            $this->actingAs($owner)->get($url)
                ->assertOk()
                ->assertSee('event-sidebar-col', false)
                ->assertDontSee('community-sidebar', false);
        }
    }

    public function test_community_event_pages_keep_the_community_sidebar(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Club', 'description' => 'Talks.']);
        $event = $this->withCategory($community->events()->create(['title' => 'Picnic', 'owner_id' => $owner->id, ...$this->eventDates()]));

        foreach ([route('events.show', $event->id), route('events.edit', $event->id)] as $url) {
            $this->actingAs($owner)->get($url)
                ->assertOk()
                ->assertSee('community-sidebar', false);
        }
    }

    public function test_pages_without_a_sidebar_section_stay_without_a_sidebar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.show', $user->id))
            ->assertOk()
            ->assertDontSee('event-sidebar-col', false);
    }
}
