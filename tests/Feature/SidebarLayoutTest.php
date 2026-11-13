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
                ->assertSee('<aside class="community-sidebar">', false);
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
                ->assertDontSee('<aside class="community-sidebar">', false);
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
                ->assertSee('<aside class="community-sidebar">', false);
        }
    }

    public function test_pages_without_a_sidebar_section_stay_without_a_sidebar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.show', $user->id))
            ->assertOk()
            ->assertDontSee('event-sidebar-col', false);
    }

    public function test_community_sidebar_links_to_the_chats_page_with_the_unread_count(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Chat Club', 'description' => 'Talks.']);
        $community->members()->attach($member->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);

        // 未読がなければ数字は出ない
        $this->actingAs($owner)
            ->get(route('community.index'))
            ->assertSee('href="' . route('community.chats') . '"', false)
            ->assertDontSee('sidebar-chat-unread">', false);

        // メンバーが 2 件送ると、オーナーのサイドバーに 2 と出る
        foreach (['Hi', 'Anyone here?'] as $message) {
            $this->actingAs($member)->postJson(route('community.chat.store', $community->id), ['message' => $message]);
        }
        $this->actingAs($owner)
            ->get(route('community.index'))
            ->assertSee('<span class="badge rounded-pill sidebar-chat-unread">2', false);

        // チャット一覧ページでもコミュニティのサイドバーが出て、Chats が選択状態になる
        $this->actingAs($owner)
            ->get(route('community.chats'))
            ->assertOk()
            ->assertSee('<aside class="community-sidebar">', false)
            ->assertSee('class="sidebar-chat-link active"', false)
            ->assertSee('Chat Club');
    }
}
