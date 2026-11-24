<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunitySearchTest extends TestCase
{
    use RefreshDatabase;

    private function communities(User $owner): void
    {
        $owner->communitiesOwned()->create(['name' => 'Morning Kickers', 'description' => 'Football every Sunday.', 'category' => 'Sports', 'location' => 'Cebu City']);
        $owner->communitiesOwned()->create(['name' => 'Page Turners', 'description' => 'We read together.', 'category' => 'Learning', 'location' => 'Tokyo']);
    }

    private function upcomingEvent(User $owner, string $communityName, string $title): Event
    {
        return Event::create([
            'owner_id' => $owner->id,
            'community_id' => $owner->communitiesOwned()->where('name', $communityName)->value('id'),
            'title' => $title,
            'description' => 'Join us.',
            'status' => 'published',
            'visibility' => 'public',
            'start_at' => now()->addDays(3),
            'join_till' => now()->addDays(2),
            'end_at' => now()->addDays(3)->addHour(),
        ]);
    }

    public function test_category_can_be_searched_by_its_name_in_any_language(): void
    {
        $owner = User::factory()->create(['preferred_language' => 'ja']);
        $this->communities($owner);

        // 画面ではスポーツ・学習と出ているので、日本語のカテゴリー名でも見つかる
        $this->actingAs($owner)->get(route('community.index', ['search' => 'スポーツ']))
            ->assertOk()->assertSee('Morning Kickers')->assertDontSee('Page Turners');
        $this->get(route('community.index', ['search' => '学習']))
            ->assertOk()->assertSee('Page Turners')->assertDontSee('Morning Kickers');
        // 英語のカテゴリー名でも今までどおり見つかる
        $this->get(route('community.index', ['search' => 'sports']))
            ->assertOk()->assertSee('Morning Kickers')->assertDontSee('Page Turners');
    }

    public function test_location_can_be_searched(): void
    {
        $owner = User::factory()->create();
        $this->communities($owner);

        $this->actingAs($owner)->get(route('community.index', ['search' => 'cebu']))
            ->assertOk()->assertSee('Morning Kickers')->assertDontSee('Page Turners');
    }

    public function test_organizer_can_be_searched_by_name_or_username(): void
    {
        $hana = User::factory()->create(['name' => 'Hana Tanaka', 'username' => 'hana_t']);
        $ken = User::factory()->create(['name' => 'Ken Sato', 'username' => 'kensato']);
        $hana->communitiesOwned()->create(['name' => 'Morning Kickers', 'description' => 'Football.']);
        $ken->communitiesOwned()->create(['name' => 'Page Turners', 'description' => 'Books.']);

        // 主催者の名前でも、ユーザー名（@ 付きでも）でも見つかる
        $this->actingAs($ken)->get(route('community.index', ['search' => 'Hana']))
            ->assertOk()->assertSee('Morning Kickers')->assertDontSee('Page Turners');
        $this->get(route('community.index', ['search' => '@kensato']))
            ->assertOk()->assertSee('Page Turners')->assertDontSee('Morning Kickers');

        // 検索バーの候補と全体検索も同じルール
        $this->getJson(route('search.suggest', ['type' => 'communities', 'search' => 'tanaka']))
            ->assertOk()->assertJsonCount(1, 'items')->assertJsonPath('items.0.title', 'Morning Kickers');
    }

    public function test_upcoming_events_follow_the_search(): void
    {
        $owner = User::factory()->create();
        $this->communities($owner);
        $this->upcomingEvent($owner, 'Morning Kickers', 'Sunday Match');
        $this->upcomingEvent($owner, 'Page Turners', 'Book Night');

        // 検索していないときは、全部のコミュニティのイベントが出る
        $this->actingAs($owner)->get(route('community.index'))
            ->assertOk()->assertSee('Sunday Match')->assertSee('Book Night');

        // 検索中は、見つかったコミュニティのイベントだけが出る
        $this->get(route('community.index', ['search' => 'Kickers']))
            ->assertOk()->assertSee('Sunday Match')->assertDontSee('Book Night')
            ->assertViewHas('upcomingEvents', fn ($events) => $events->pluck('title')->all() === ['Sunday Match']);

        // 見つかったコミュニティにイベントが無いときは、検索に合わせた文を出す
        $this->get(route('community.index', ['search' => 'nothing-matches']))
            ->assertOk()->assertSee('No upcoming events from these communities.');
    }
}
