<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_feed_links_to_searchable_people_excluding_self_and_blocks(): void
    {
        $viewer = User::factory()->create();
        $person = User::factory()->create(['name' => str_repeat('LongName', 20), 'username' => 'discover_me']);
        $blocked = User::factory()->create();
        \App\Models\UserBlock::block($blocked->id, $viewer->id);
        $this->actingAs($viewer)->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'feed']))
            ->assertOk()->assertSee('Follow people to see their public posts here.')->assertSee('Find people');
        $url = route('profile.show', ['id' => $viewer->id, 'connections' => 'people', 'tab' => 'feed']);
        $this->get($url)->assertOk()->assertViewHas('connections', fn ($people) => $people->total() === 1 && $people->first()->id === $person->id);
        $data = ['connections_owner' => $viewer->id, 'tab' => 'feed', 'connections' => 'people'];
        $this->post(route('profile.follow', $person), $data)->assertRedirect($url);
        $this->get($url)->assertOk()->assertViewHas('user', fn ($user) => $user->following_count === 1)
            ->assertViewHas('connections', fn ($people) => $people->first()->followed_by_viewer);
        $this->delete(route('profile.unfollow', $person), $data)->assertRedirect($url);
        $this->get($url)->assertOk()->assertViewHas('user', fn ($user) => $user->following_count === 0)
            ->assertViewHas('connections', fn ($people) => !$people->first()->followed_by_viewer);
    }

    public function test_unfollowing_last_person_on_last_page_returns_to_valid_page(): void
    {
        $viewer = User::factory()->create();
        $people = User::factory()->count(21)->create();
        $viewer->following()->attach($people->pluck('id'), ['created_at' => now()]);
        $url = route('profile.show', ['id' => $viewer->id, 'connections' => 'following', 'connections_page' => 2]);
        $response = $this->actingAs($viewer)->get($url)->assertOk();
        $person = $response->viewData('connections')->first();
        $this->delete(route('profile.unfollow', $person), ['connections_owner' => $viewer->id, 'connections' => 'following', 'connections_page' => 2])->assertRedirect();
        $this->get($url)->assertRedirect(route('profile.show', ['id' => $viewer->id, 'tab' => 'event', 'connections' => 'following', 'connection_search' => '', 'connections_page' => 1]));
        $this->get(route('profile.show', ['id' => $viewer->id, 'connections' => 'following']))
            ->assertOk()->assertViewHas('user', fn ($user) => $user->following_count === 20)
            ->assertViewHas('connections', fn ($users) => $users->total() === 20);
    }

    public function test_connections_search_relationships_and_follow_return_to_modal(): void
    {
        $viewer = User::factory()->create();
        $person = User::factory()->create(['name' => 'Searchable Friend', 'username' => 'find_friend']);
        $person->profile()->create(['bio' => 'Enjoys coffee and hiking.']);
        $viewer->followers()->attach($person->id, ['created_at' => now()]);
        $url = route('profile.show', ['id' => $viewer->id, 'connections' => 'followers', 'connection_search' => 'find_friend', 'tab' => 'event']);
        $this->actingAs($viewer)->get($url)->assertOk()->assertSee('Follows you')->assertDontSee('Enjoys coffee and hiking.')
            ->assertViewHas('connections', fn ($users) => $users->total() === 1 && !$users->first()->followed_by_viewer && $users->first()->follows_viewer);
        $data = ['connections_owner' => $viewer->id, 'connections' => 'followers', 'connection_search' => 'find_friend', 'tab' => 'event'];
        $this->post(route('profile.follow', $person), $data)->assertRedirect($url);
        $this->get($url)->assertOk()->assertSee('Mutual')->assertSee('Unfollow');
        $this->delete(route('profile.unfollow', $person), $data)->assertRedirect($url);
        $this->get(route('profile.show', ['id' => $viewer->id, 'connections' => 'followers', 'connection_search' => 'nobody matches']))
            ->assertOk()->assertSee('No users found.')->assertViewHas('connections', fn ($users) => $users->total() === 0);
    }

    public function test_other_profiles_hide_sidebar_and_show_their_own_sections(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $this->actingAs($viewer)->get(route('profile.show', $owner))
            ->assertOk()->assertViewHas('activeTab', 'event')
            ->assertDontSee('aria-label="Personal navigation"', false)
            ->assertSee('profile-visitor')
            ->assertSee(route('profile.show', ['id' => $owner->id, 'tab' => 'review']))
            ->assertSee('User profile sections')->assertDontSee('Block list')
            ->assertDontSee('data-profile-panel="history"', false)
            ->assertDontSee('data-profile-panel="bookmark"', false);
        $this->get(route('profile.show', ['id' => $owner->id, 'tab' => 'feed']))
            ->assertRedirect(route('profile.show', ['id' => $viewer->id, 'tab' => 'feed']));
        $this->get(route('profile.show', $viewer))
            ->assertOk()->assertSee('aria-label="Personal navigation"', false)
            ->assertDontSee('profile-visitor');
    }

    public function test_profile_feed_shows_public_posts_from_followed_users_only(): void
    {
        $viewer = User::factory()->create();
        $follower = User::factory()->create();
        $followed = User::factory()->create();
        $viewer->followers()->attach($follower->id, ['created_at' => now()]);
        $viewer->following()->attach($followed->id, ['created_at' => now()]);
        $attributes = ['start_at' => now()->addDay(), 'end_at' => now()->addDays(2), 'join_till' => now()];
        foreach ([[$follower, 'public', 'published'], [$follower, 'private', 'published'], [$follower, 'public', 'draft'], [$followed, 'public', 'published']] as [$owner, $visibility, $status]) {
            Event::create($attributes + ['owner_id' => $owner->id, 'title' => 'Feed post', 'visibility' => $visibility, 'status' => $status]);
        }
        $this->actingAs($viewer)->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'profile']))
            ->assertOk()->assertSee('Posts from people you follow')
            ->assertViewHas('followingEvents', fn ($events) => $events->total() === 1 && $events->first()->owner_id === $followed->id);
        $this->get(route('profile.show', $followed))
            ->assertOk()->assertViewHas('followingEvents', fn ($events) => $events->total() === 1 && $events->first()->owner_id === $followed->id);
    }

    public function test_editor_block_list_only_shows_current_users_active_blocks_and_can_unblock(): void
    {
        $viewer = User::factory()->create();
        $blocked = User::factory()->create(['name' => 'Blocked Person']);
        $other = User::factory()->create(['name' => 'Other Private Block']);
        $expired = User::factory()->create(['name' => 'Expired Block']);
        \App\Models\UserBlock::block($blocked->id, $viewer->id);
        \App\Models\UserBlock::block($other->id, $blocked->id);
        \App\Models\UserBlock::block($expired->id, $viewer->id)->update(['expires_at' => now()->subDay()]);

        $url = route('profile.edit', ['tab' => 'blocked']);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($viewer)->get(route('profile.edit'))->assertOk()->assertSee('Block list');
        $this->get($url)->assertOk()->assertSee('Blocked Person')->assertSee('Unblock')
            ->assertDontSee('Other Private Block')->assertDontSee('Expired Block')
            ->assertViewHas('blockedUsers', fn ($blocks) => $blocks->total() === 1);
        $this->from($url)->delete(route('messages.unblock', $blocked))->assertRedirect($url);
        $this->get($url)->assertOk()->assertSee("You haven't blocked anyone.", false);
        $this->assertDatabaseHas('user_blocks', ['blocked_by' => $blocked->id, 'user_id' => $other->id]);
        $this->get(route('profile.show', $viewer))->assertOk()->assertDontSee('Block list');
    }

    public function test_community_tab_lists_approved_memberships_and_hides_private_communities_from_others(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        foreach (['public', 'private', 'pending'] as $type) {
            $community = \App\Models\Community::create([
                'owner_id' => $viewer->id, 'name' => 'Joined community '.$type,
                'visibility' => $type === 'private' ? 'private' : 'public',
            ]);
            $owner->communities()->attach($community, [
                'status' => $type === 'pending' ? 'pending' : 'approved', 'joined_at' => now(),
            ]);
        }
        $this->actingAs($owner)->get(route('profile.show', ['id' => $owner->id, 'tab' => 'community']))
            ->assertOk()->assertSee('Joined community public')->assertSee('Joined community private')
            ->assertDontSee('Joined community pending')
            ->assertViewHas('joinedCommunities', fn ($communities) => $communities->total() === 2);
        $this->actingAs($viewer)->get(route('profile.show', ['id' => $owner->id, 'tab' => 'community']))
            ->assertOk()->assertSee('Joined community public')->assertDontSee('Joined community private')
            ->assertViewHas('joinedCommunities', fn ($communities) => $communities->total() === 1);
        $this->get(route('profile.show', $viewer->id))->assertOk()->assertSee('No joined communities yet.');
    }

    public function test_history_lists_the_owners_joined_and_pending_events(): void
    {
        $viewer = User::factory()->create();
        $host = User::factory()->create();
        foreach (['approved', 'pending', 'rejected'] as $status) {
            $event = Event::create([
                'owner_id' => $host->id, 'title' => 'History '.$status,
                'start_at' => now()->subDay(), 'end_at' => now(), 'join_till' => now()->subDays(2),
            ]);
            $viewer->attendingEvents()->attach($event, ['status' => $status]);
            if ($status === 'approved') {
                $viewer->bookmarkedEvents()->attach($event);
            }
        }

        $this->actingAs($viewer)->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'history']))
            ->assertOk()->assertSee('History approved')->assertSee('History pending')->assertSee('Pending approval')
            ->assertDontSee('History rejected')
            ->assertViewHas('historyEvents', fn ($events) => $events->total() === 2
                && (bool) $events->firstWhere('title', 'History approved')->is_bookmarked);
        $this->actingAs($host)->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'history']))
            ->assertRedirect(route('profile.show', ['id' => $host->id, 'tab' => 'history']));
        $this->get(route('profile.show', ['id' => $host->id, 'tab' => 'history']))
            ->assertOk()->assertSee('No joined events yet.');
    }

    public function test_history_pagination_preserves_the_history_tab_and_date_order(): void
    {
        $viewer = User::factory()->create();
        $host = User::factory()->create();
        for ($i = 0; $i < 13; $i++) {
            $event = Event::create([
                'owner_id' => $host->id, 'title' => 'Joined event '.$i,
                'start_at' => now()->subDays($i + 1), 'end_at' => now()->subDays($i),
                'join_till' => now()->subDays(20),
            ]);
            $viewer->attendingEvents()->attach($event, ['status' => 'approved']);
        }
        $this->actingAs($viewer)->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'history']))
            ->assertOk()->assertSee('tab=history', false)
            ->assertViewHas('historyEvents', fn ($events) => $events->total() === 13
                && $events->count() === 12 && $events->first()->title === 'Joined event 0');
        $this->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'history', 'history_page' => 2]))
            ->assertOk()->assertSee('Joined event 12')
            ->assertViewHas('historyEvents', fn ($events) => $events->count() === 1);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_profile_displays_saved_details_and_owner_edit_link(): void
    {
        $user = User::factory()->create(['username' => 'surfer']);
        $user->profile()->create(['bio' => 'Enjoying surfing in Cebu.', 'avatar' => 'avatars/surfer.jpg']);

        $this->actingAs($user)->get(route('profile.show', $user->id))
            ->assertOk()
            ->assertSee('@surfer')
            ->assertSee('Enjoying surfing in Cebu.')
            ->assertSee(asset('storage/avatars/surfer.jpg'))
            ->assertSee('Edit Profile')
            ->assertSee('Events Hosted');
    }

    public function test_other_users_have_no_edit_link_and_missing_profiles_render(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();

        $this->actingAs($viewer)->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSee($owner->name)
            ->assertDontSee('Edit Profile')
            ->assertSee('No events shared yet.')
            ->assertDontSee('Surfing by the ocean');

        $this->get(route('profile.show', 99999))->assertNotFound();
    }
    public function test_profile_shows_the_users_event_posts(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $attributes = [
            'start_at' => now()->addDay(),
            'join_till' => now(),
            'end_at' => now()->addDays(2),
        ];
        Event::create($attributes + [
            'owner_id' => $owner->id,
            'title' => 'Cebu surfing meetup',
            'description' => 'Join us at the beach.',
            'image' => 'events/surfing.jpg',
            'location' => 'Cebu',
        ]);
        Event::create($attributes + ['owner_id' => $other->id, 'title' => 'Another user event']);

        $this->actingAs($owner)->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSee('Cebu surfing meetup')
            ->assertSee('Join us at the beach.')
            ->assertSee(asset('storage/events/surfing.jpg'))
            ->assertDontSee('Another user event')
            ->assertDontSee('No events shared yet.')
            ->assertDontSee('Surfing by the ocean');
    }

    public function test_hosted_event_cards_preserve_external_image_urls(): void
    {
        $owner = User::factory()->create();
        Event::create([
            'owner_id' => $owner->id, 'title' => 'External image event',
            'image' => 'https://example.com/event.jpg',
            'start_at' => now()->addDay(), 'join_till' => now(), 'end_at' => now()->addDays(2),
        ]);

        $this->actingAs($owner)->get(route('profile.show', $owner->id))
            ->assertOk()->assertSee('src="https://example.com/event.jpg"', false)
            ->assertDontSee('storage/https://example.com/event.jpg');
    }

    public function test_edit_form_and_profile_updates_preserve_unedited_details(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $user = User::factory()->create();
        $user->profile()->create(['occupation' => 'Teacher', 'region' => 'Cebu']);
        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()->assertSee('Profile Preview')->assertSee('Save Changes');

        $this->patch(route('profile.profile.update'), [
            'name' => 'Updated Name', 'username' => 'updated_surfer',
            'preferred_language' => 'ja', 'country' => 'Japan', 'bio' => 'Hello Cebu!',
            'avatar' => \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg'),
            'cover_image' => \Illuminate\Http\UploadedFile::fake()->image('cover.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.show', $user->id));

        $user->refresh();
        $this->assertSame('updated_surfer', $user->username);
        $this->assertSame('ja', $user->preferred_language);
        $this->assertSame('Japan', $user->profile->country);
        $this->assertSame('Hello Cebu!', $user->profile->bio);
        $this->assertSame('Teacher', $user->profile->occupation);
        $this->assertSame('Cebu', $user->profile->region);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile->avatar));
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile->cover_image));
    }

    public function test_profile_details_can_be_saved_and_cleared(): void
    {
        $user = User::factory()->create();
        $payload = [
            'name' => $user->name, 'username' => $user->username,
            'preferred_language' => 'en', 'region' => 'Cebu',
            'occupation' => 'Teacher', 'gender' => 'other',
            'birthday' => now()->subYears(25)->toDateString(), 'age' => 99,
        ];

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()
            ->assertSee('name="region"', false)->assertSee('name="occupation"', false)
            ->assertSee('name="birthday_month"', false)->assertSee('name="gender"', false);
        $this->patch(route('profile.profile.update'), $payload)->assertSessionHasNoErrors();
        $profile = $user->fresh()->profile;
        $this->assertSame('Cebu', $profile->region);
        $this->assertSame('Teacher', $profile->occupation);
        $this->assertSame('other', $profile->gender);
        $this->assertEquals(25, $profile->age);

        $this->patch(route('profile.profile.update'), array_replace($payload, [
            'region' => '', 'occupation' => '', 'gender' => '', 'birthday' => '',
        ]))->assertSessionHasNoErrors();
        $profile->refresh();
        foreach (['region', 'occupation', 'gender', 'birthday', 'age'] as $field) {
            $this->assertNull($profile->$field);
        }
    }

    public function test_english_birthday_fields_save_and_validate_calendar_dates(): void
    {
        $user = User::factory()->create();
        $payload = ['name' => $user->name, 'username' => $user->username,
            'preferred_language' => 'en', 'birthday_year' => '2000',
            'birthday_month' => '2', 'birthday_day' => '29'];
        $this->actingAs($user)->patch(route('profile.profile.update'), $payload)
            ->assertSessionHasNoErrors();
        $this->assertSame('2000-02-29', $user->fresh()->profile->birthday->format('Y-m-d'));
        $this->get(route('profile.edit'))->assertOk()->assertSee('February');
        $this->patch(route('profile.profile.update'), array_replace($payload, ['birthday_day' => '30']))
            ->assertSessionHasErrors('birthday');
        $this->patch(route('profile.profile.update'), array_replace($payload, ['birthday_month' => '']))
            ->assertSessionHasErrors('birthday_month');
        $this->patch(route('profile.profile.update'), array_replace($payload, [
            'birthday_year' => '', 'birthday_month' => '', 'birthday_day' => '',
        ]))->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->profile->birthday);
    }

    public function test_future_birthday_and_invalid_profile_fields_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.profile.update'), [
            'name' => $user->name, 'username' => $user->username,
            'preferred_language' => 'en', 'birthday' => now()->addDay()->toDateString(),
            'gender' => 'invalid', 'region' => str_repeat('a', 101),
            'occupation' => str_repeat('a', 101),
        ])->assertSessionHasErrors(['birthday', 'gender', 'region', 'occupation']);
    }

    public function test_invalid_profile_changes_are_rejected(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.profile.update'), [
            'name' => 'Changed', 'username' => $other->username,
            'preferred_language' => 'invalid', 'bio' => str_repeat('a', 301),
        ])->assertSessionHasErrors(['username', 'preferred_language', 'bio']);
        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_other_users_only_see_public_published_events(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $attributes = [
            'owner_id' => $owner->id,
            'start_at' => now()->addDay(),
            'join_till' => now(),
            'end_at' => now()->addDays(2),
        ];
        Event::create($attributes + ['title' => 'Public published event', 'visibility' => 'public', 'status' => 'published']);
        Event::create($attributes + ['title' => 'Private published event', 'visibility' => 'private', 'status' => 'published']);
        Event::create($attributes + ['title' => 'Public draft event', 'visibility' => 'public', 'status' => 'draft']);

        $this->actingAs($viewer)->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSee('Public published event')
            ->assertDontSee('Private published event')
            ->assertDontSee('Public draft event')
            ->assertViewHas('user', fn ($user) => $user->events_owned_count === 1)
            ->assertViewHas('events', fn ($events) => $events->count() === 1);

        $this->actingAs($owner)->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSee('Public published event')
            ->assertSee('Private published event')
            ->assertSee('Public draft event')
            ->assertViewHas('user', fn ($user) => $user->events_owned_count === 3)
            ->assertViewHas('events', fn ($events) => $events->count() === 3);
    }

    public function test_navbar_uses_storage_url_for_uploaded_avatar(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['avatar' => 'avatars/navbar.jpg']);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('src="'.asset('storage/avatars/navbar.jpg').'" alt="'.$user->name.'"', false);
    }

    public function test_saved_hearts_appear_only_in_the_owners_profile_bookmarks(): void
    {
        $viewer = User::factory()->create();
        $host = User::factory()->create();
        $event = Event::create([
            'owner_id' => $host->id, 'title' => 'Saved heart event',
            'start_at' => now()->addDay(), 'join_till' => now(),
            'end_at' => now()->addDays(2),
        ]);

        $this->actingAs($viewer)->post(route('bookmarks.store', $event))->assertRedirect();
        $this->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'bookmark']))
            ->assertOk()->assertSee('Saved heart event')
            ->assertViewHas('bookmarkedEvents', fn ($events) => $events->total() === 1);

        $this->actingAs($host)->get(route('profile.show', $viewer->id))
            ->assertOk()->assertDontSee('Saved heart event')
            ->assertDontSee('Bookmarks are private.')
            ->assertViewHas('bookmarkedEvents', null);

        $this->actingAs($viewer)->delete(route('bookmarks.destroy', $event))->assertRedirect();
        $this->get(route('profile.show', $viewer->id))
            ->assertOk()->assertDontSee('Saved heart event')
            ->assertSee('No bookmarked events yet.');
    }

    public function test_profile_bookmark_pagination_keeps_the_bookmark_tab(): void
    {
        $viewer = User::factory()->create();
        $host = User::factory()->create();
        for ($i = 0; $i < 13; $i++) {
            $event = Event::create([
                'owner_id' => $host->id, 'title' => 'Bookmark '.$i,
                'start_at' => now()->addDay(), 'join_till' => now(),
                'end_at' => now()->addDays(2),
            ]);
            $viewer->bookmarkedEvents()->attach($event->id, ['created_at' => now()->addSeconds($i)]);
        }
        $this->actingAs($viewer)->get(route('profile.show', $viewer->id))
            ->assertOk()->assertSee('tab=bookmark', false)
            ->assertViewHas('bookmarkedEvents', fn ($events) => $events->total() === 13 && $events->count() === 12);
        $this->get(route('profile.show', ['id' => $viewer->id, 'tab' => 'bookmark', 'bookmarks_page' => 2]))
            ->assertOk()->assertSee('Bookmark 0')
            ->assertViewHas('bookmarkedEvents', fn ($events) => $events->count() === 1);
    }

    public function test_follow_unfollow_and_connection_lists(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $this->actingAs($viewer)->get(route('profile.show', $owner))
            ->assertOk()->assertSee('<span>Follow</span>', false);
        $this->post(route('profile.follow', $owner))->assertRedirect(route('profile.show', $owner));
        $this->post(route('profile.follow', $owner))->assertRedirect();
        $this->assertDatabaseCount('follows', 1);
        $this->get(route('profile.show', ['id' => $owner->id, 'connections' => 'followers']))
            ->assertOk()->assertSee('Unfollow')->assertSee($viewer->name)
            ->assertViewHas('connections', fn ($users) => $users->total() === 1 && $users->first()->id === $viewer->id)
            ->assertViewHas('user', fn ($user) => $user->followers_count === 1);
        $this->get(route('profile.show', ['id' => $viewer->id, 'connections' => 'following']))
            ->assertOk()->assertViewHas('connections', fn ($users) => $users->first()->id === $owner->id);
        $this->delete(route('profile.unfollow', $owner))->assertRedirect();
        $this->delete(route('profile.unfollow', $owner))->assertRedirect();
        $this->assertDatabaseCount('follows', 0);
        $this->get(route('profile.show', ['id' => $owner->id, 'connections' => 'followers']))
            ->assertOk()->assertSee('No followers yet.');
    }

    public function test_follow_rejects_self_and_blocks_in_either_direction(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $this->actingAs($viewer)->post(route('profile.follow', $viewer))->assertForbidden();
        foreach ([[$owner->id, $viewer->id], [$viewer->id, $owner->id]] as [$target, $blocker]) {
            \App\Models\UserBlock::block($target, $blocker);
            $this->post(route('profile.follow', $owner))->assertForbidden();
            $response = $this->get(route('profile.show', $owner))->assertOk();
            $this->assertMatchesRegularExpression('/<button\b[^>]*\bdisabled\b[^>]*><i\b[^>]*><\/i><span>Follow<\/span><\/button>/', $response->getContent());
            \App\Models\UserBlock::unblock($target, $blocker);
        }
        $this->assertDatabaseCount('follows', 0);
    }

    public function test_guests_cannot_follow_and_unfollow_only_changes_the_current_users_relationship(): void
    {
        $viewer = User::factory()->create();
        $other = User::factory()->create();
        $owner = User::factory()->create();
        $this->post(route('profile.follow', $owner))->assertRedirect(route('login'));
        $this->delete(route('profile.unfollow', $owner))->assertRedirect(route('login'));
        \App\Models\Follow::create(['follower_id' => $other->id, 'following_id' => $owner->id]);
        $this->actingAs($viewer)->delete(route('profile.unfollow', $owner))->assertRedirect();
        $this->assertDatabaseHas('follows', ['follower_id' => $other->id, 'following_id' => $owner->id]);
        $this->post(route('profile.follow', 999999))->assertNotFound();
    }

}
