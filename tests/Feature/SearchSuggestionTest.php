<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use App\Support\Search;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSuggestionTest extends TestCase
{
    use RefreshDatabase;

    private function event(User $owner, string $title, array $attributes = []): Event
    {
        return Event::create(array_merge([
            'owner_id' => $owner->id,
            'title' => $title,
            'description' => 'Come along.',
            'location' => 'Cebu City',
            'status' => 'published',
            'visibility' => 'public',
            'start_at' => now()->addDays(3),
            'join_till' => now()->addDays(2),
            'end_at' => now()->addDays(3)->addHour(),
        ], $attributes));
    }

    private function suggest(string $type, string $search)
    {
        return $this->getJson(route('search.suggest', ['type' => $type, 'search' => $search]))->assertOk();
    }

    public function test_words_are_trimmed_split_and_limited(): void
    {
        $this->assertSame([], Search::words('   '));
        $this->assertSame(['baseball', 'cebu'], Search::words('  baseball   cebu '));
        $this->assertCount(5, Search::words('a b c d e f g'));
    }

    public function test_every_word_must_match(): void
    {
        $owner = User::factory()->create();
        $owner->communitiesOwned()->create(['name' => 'Morning Kickers', 'description' => 'Football.', 'location' => 'Cebu City']);
        $owner->communitiesOwned()->create(['name' => 'Evening Kickers', 'description' => 'Football.', 'location' => 'Tokyo']);

        $this->actingAs($owner);
        $this->suggest('communities', 'kickers cebu')
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'Morning Kickers')
            ->assertJsonPath('items.0.url', route('community.show', $owner->communitiesOwned()->where('name', 'Morning Kickers')->value('id')));
    }

    public function test_private_communities_and_hidden_events_are_not_suggested(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $owner->communitiesOwned()->create(['name' => 'Secret Garden', 'description' => 'Invite only.', 'visibility' => 'private']);
        $this->event($owner, 'Garden Party');
        $this->event($owner, 'Garden Draft', ['status' => 'draft']);
        $this->event($owner, 'Garden Private', ['visibility' => 'private']);

        // 関係ない人には、招待のみのコミュニティ・下書き・非公開のイベントは出ない
        $this->actingAs($stranger);
        $titles = collect($this->suggest('all', 'garden')->json('items'))->pluck('title')->all();
        $this->assertSame(['Garden Party'], $titles);

        // 本人には自分のものが全部出る
        $this->actingAs($owner);
        $titles = collect($this->suggest('all', 'garden')->json('items'))->pluck('title')->all();
        $this->assertContains('Secret Garden', $titles);
        $this->assertContains('Garden Draft', $titles);
        $this->assertContains('Garden Private', $titles);
    }

    public function test_event_category_can_be_found_in_any_language(): void
    {
        $owner = User::factory()->create();
        $sports = Category::create(['name' => 'Sports']);
        $this->event($owner, 'Sunday Match')->categories()->attach($sports->id);
        $this->event($owner, 'Book Night');

        $this->actingAs($owner);
        $this->suggest('events', 'スポーツ')
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.title', 'Sunday Match');
    }

    public function test_global_search_page_uses_the_same_rules(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $owner->communitiesOwned()->create(['name' => 'Secret Garden', 'description' => 'Invite only.', 'visibility' => 'private']);
        $owner->communitiesOwned()->create(['name' => 'Open Garden', 'description' => 'Everyone welcome.']);
        $this->event($owner, 'Garden Draft', ['status' => 'draft']);

        $this->actingAs($stranger)
            ->get(route('search', ['search' => 'garden']))
            ->assertOk()
            ->assertSee('Open Garden')
            ->assertDontSee('Secret Garden')
            ->assertDontSee('Garden Draft');
    }

    public function test_user_search_shows_usernames_while_profiles_keep_names(): void
    {
        $viewer = User::factory()->create();
        $person = User::factory()->create(['name' => 'Private Display Name', 'username' => 'public_handle']);

        $this->actingAs($viewer)
            ->get(route('search', ['search' => 'public_handle']))
            ->assertOk()
            ->assertSee('@public_handle')
            ->assertDontSee('Private Display Name');

        $this->suggest('all', 'public_handle')
            ->assertJsonPath('items.0.title', '@public_handle');

        $this->get(route('profile.show', $person))
            ->assertOk()
            ->assertSee('Private Display Name');
    }

    public function test_events_page_search_uses_location_and_every_word(): void
    {
        $owner = User::factory()->create();
        $this->event($owner, 'Beach Cleanup', ['location' => 'Mactan']);
        $this->event($owner, 'Beach Volleyball', ['location' => 'Cebu City']);

        $this->actingAs($owner)
            ->get(route('events.index', ['keyword' => 'beach mactan']))
            ->assertOk()
            ->assertSee('Beach Cleanup')
            ->assertDontSee('Beach Volleyball');
    }

    public function test_search_inputs_show_suggestions(): void
    {
        $user = User::factory()->create();

        // どの検索バーにも、候補を出すための印が付いている
        $this->actingAs($user)->get(route('community.index'))
            ->assertSee('data-search-suggest="communities"', false)
            ->assertSee('data-search-suggest="all"', false);
        $this->get(route('events.index'))->assertSee('data-search-suggest="events"', false);
        $this->get(route('helps.index'))->assertSee('data-search-suggest="helps"', false);
    }

    public function test_empty_or_too_long_searches(): void
    {
        $this->suggest('all', '   ')->assertExactJson(['items' => []]);
        $this->getJson(route('search.suggest', ['search' => str_repeat('a', 101)]))->assertUnprocessable();
        $this->getJson(route('search.suggest', ['type' => 'secret', 'search' => 'a']))->assertUnprocessable();
    }
}
