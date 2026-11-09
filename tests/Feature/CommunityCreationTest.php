<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunityCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_is_saved_as_community_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('community.store'), [
                'name' => 'Pickle Ball',
                'category' => 'Sports',
                'visibility' => 'public',
                'description' => 'A local pickle ball community.',
                'owner_id' => $otherUser->id,
            ])
            ->assertRedirect(route('community.index'));

        $community = Community::where('name', 'Pickle Ball')->firstOrFail();

        $this->assertSame($owner->id, $community->owner_id);
    }

    public function test_uploaded_community_images_are_saved(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('community.store'), [
                'name' => 'Photo Community',
                'category' => 'Creative',
                'location' => 'Cebu',
                'description' => 'A community with images.',
                'visibility' => 'approval',
                'guidelines' => 'Be kind and welcoming.',
                'icon' => UploadedFile::fake()->createWithContent(
                    'icon.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
                ),
                'cover_image' => UploadedFile::fake()->createWithContent(
                    'cover.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect(route('community.index'));

        $community = Community::where('name', 'Photo Community')->firstOrFail();

        Storage::disk('public')->assertExists([
            $community->icon,
            $community->image,
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('storage/' . $community->icon, false)
            ->assertSee('storage/' . $community->image, false);

        $this->get(route('community.show', $community->id))
            ->assertOk()
            ->assertSee('Photo Community')
            ->assertSee('Creative')
            ->assertSee('Cebu')
            ->assertSee('Be kind and welcoming.')
            ->assertSee('Approval required')
            ->assertSee('storage/' . $community->icon, false)
            ->assertSee('storage/' . $community->image, false);
    }

    public function test_community_detail_page_receives_the_community(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create([
            'name' => 'Detail Community',
            'description' => 'A community detail page test.',
        ]);

        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertOk()
            ->assertSee('Detail Community')
            ->assertSee('A community detail page test.');
    }

    public function test_create_form_shows_native_image_upload_inputs(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('community.create'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('id="community-icon" name="icon" type="file"', false)
            ->assertSee('id="community-cover-image" name="cover_image" type="file"', false);
    }

    public function test_owner_can_delete_a_community_from_its_edit_page(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create([
            'name' => 'Community to delete',
            'description' => 'This community will be deleted.',
        ]);

        $this->actingAs($owner)
            ->get(route('community.edit', $community->id))
            ->assertOk()
            ->assertSee(route('community.destroy', $community->id), false)
            ->assertSee('Delete community');

        $this->delete(route('community.destroy', $community->id))
            ->assertRedirect(route('community.index'));

        $this->assertSoftDeleted('communities', ['id' => $community->id]);
    }

    public function test_non_owner_cannot_delete_a_community(): void
    {
        $community = User::factory()->create()->communitiesOwned()->create([
            'name' => 'Another users community',
            'description' => 'Only its owner may delete this.',
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('community.destroy', $community->id))
            ->assertForbidden();

        $this->assertNotSoftDeleted('communities', ['id' => $community->id]);
    }

    public function test_owner_can_update_community_details_shown_on_detail_page(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create([
            'name' => 'Old name',
            'description' => 'Old description.',
            'category' => 'Creative',
            'visibility' => 'public',
        ]);

        $this->actingAs($owner)
            ->put(route('community.update', $community->id), [
                'name' => 'Updated community',
                'category' => 'Learning',
                'location' => 'Online',
                'description' => 'Updated description.',
                'visibility' => 'private',
                'guidelines' => 'Respect all members.',
            ])
            ->assertRedirect(route('community.show', $community->id));

        $this->get(route('community.show', $community->id))
            ->assertOk()
            ->assertSee('Updated community')
            ->assertSee('Learning')
            ->assertSee('Online')
            ->assertSee('Updated description.')
            ->assertSee('Private')
            ->assertSee('Respect all members.');
    }

    public function test_community_event_post_is_saved_and_shown_in_index_upcoming_events(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create([
            'name' => 'Event Hosting Community',
            'description' => 'A community that hosts events.',
        ]);

        $this->actingAs($owner)
            ->post(route('community.events.store', $community->id), $this->eventFormData([
                'title' => 'Community Workshop',
                'description' => 'A hands-on workshop.',
                'location' => 'Community Hall',
            ]))
            ->assertRedirect(route('community.show', $community->id));

        $event = Event::where('title', 'Community Workshop')->firstOrFail();
        $this->assertSame($community->id, $event->community_id);
        $this->assertSame($owner->id, $event->owner_id);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Community Workshop')
            ->assertSee('Event Hosting Community')
            ->assertSee('Community Hall');
    }

    public function test_creating_shows_no_success_message(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('community.store'), [
                'name' => 'Quiet Community',
                'category' => 'Sports',
                'visibility' => 'public',
                'description' => 'Shows no message after creating.',
            ])
            ->assertRedirect(route('community.index'))
            ->assertSessionMissing('success');

        $community = Community::where('name', 'Quiet Community')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('community.events.store', $community->id), $this->eventFormData())
            ->assertRedirect(route('community.show', $community->id))
            ->assertSessionMissing('success');

        $this->actingAs($owner)
            ->post(route('events.store'), $this->eventFormData())
            ->assertSessionMissing('success');
    }

    public function test_edit_links_are_only_shown_to_the_owner(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create([
            'name' => 'Owner Only Edit',
            'description' => 'Edit links are for the owner.',
        ]);
        $editUrl = route('community.edit', $community->id);

        $this->actingAs(User::factory()->create())
            ->get(route('community.index'))
            ->assertOk()
            ->assertDontSee($editUrl, false);
        $this->get(route('community.show', $community->id))
            ->assertOk()
            ->assertDontSee($editUrl, false);

        $this->actingAs($owner)
            ->get(route('community.index'))
            ->assertSee($editUrl, false);
        $this->get(route('community.show', $community->id))
            ->assertSee($editUrl, false);
    }

    public function test_detail_page_lists_approved_members_in_members_tab(): void
    {
        $owner = User::factory()->create(['name' => 'Owner Person']);
        $community = $owner->communitiesOwned()->create([
            'name' => 'Members Community',
            'description' => 'Has members.',
        ]);
        $approved = User::factory()->create(['name' => 'Approved Person']);
        $pending = User::factory()->create(['name' => 'Pending Person']);
        $community->members()->attach($approved->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $community->members()->attach($pending->id, ['role' => 'member', 'status' => 'pending']);

        $html = $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertOk()
            ->assertSee('id="members"', false)
            ->getContent();

        // レイアウトのメッセージ一覧にも全ユーザー名が出るため、Members タブの中だけを確認する
        // 申請中の人はオーナーには「Join requests」に出て、メンバー一覧には出ない
        $requests = Str::between($html, 'Join requests', 'community-member-list');
        $memberList = Str::between($html, 'community-member-list', 'id="about"');

        $this->assertStringContainsString('Pending Person', $requests);
        $this->assertStringContainsString('Owner Person', $memberList);
        $this->assertStringContainsString('Approved Person', $memberList);
        $this->assertStringNotContainsString('Pending Person', $memberList);
    }

    public function test_replacing_community_icon_deletes_the_old_file(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $oldIcon = 'communities/icons/old.png';
        Storage::disk('public')->put($oldIcon, 'old icon');
        $community = $owner->communitiesOwned()->create([
            'name' => 'Icon Community',
            'description' => 'Replaces its icon.',
            'icon' => $oldIcon,
        ]);

        $this->actingAs($owner)
            ->put(route('community.update', $community->id), [
                'name' => 'Icon Community',
                'category' => 'Creative',
                'description' => 'Replaces its icon.',
                'visibility' => 'public',
                'icon' => UploadedFile::fake()->createWithContent(
                    'new.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect(route('community.show', $community->id));

        Storage::disk('public')->assertMissing($oldIcon);
        Storage::disk('public')->assertExists($community->fresh()->icon);
    }

    public function test_index_search_filters_communities(): void
    {
        $owner = User::factory()->create();
        $owner->communitiesOwned()->create(['name' => 'Tennis Lovers', 'description' => 'Weekend tennis.']);
        $owner->communitiesOwned()->create(['name' => 'Book Club', 'description' => 'Monthly reading.']);

        $this->actingAs($owner)
            ->get(route('community.index', ['search' => 'tennis']))
            ->assertOk()
            ->assertSee('Tennis Lovers')
            ->assertDontSee('Book Club');
    }

    public function test_sidebar_filters_joined_and_owned_communities(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->communitiesOwned()->create(['name' => 'Owned Garden', 'description' => 'Mine.']);
        $joined = $other->communitiesOwned()->create(['name' => 'Joined Choir', 'description' => 'Approved.']);
        $pending = $other->communitiesOwned()->create(['name' => 'Pending Chess', 'description' => 'Not yet.']);
        $other->communitiesOwned()->create(['name' => 'Stranger Hiking', 'description' => 'Not joined.']);
        $joined->members()->attach($user->id, ['role' => 'member', 'status' => 'approved', 'joined_at' => now()]);
        $pending->members()->attach($user->id, ['role' => 'member', 'status' => 'pending']);

        $this->actingAs($user)
            ->get(route('community.index', ['filter' => 'joined']))
            ->assertOk()
            ->assertSee('Owned Garden')
            ->assertSee('Joined Choir')
            ->assertDontSee('Pending Chess')
            ->assertDontSee('Stranger Hiking')
            ->assertSee('Joined Communities');

        $this->actingAs($user)
            ->get(route('community.index', ['filter' => 'owned']))
            ->assertOk()
            ->assertSee('Owned Garden')
            ->assertDontSee('Joined Choir')
            ->assertDontSee('Stranger Hiking');
    }

    public function test_community_events_are_not_listed_on_events_page(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Event Club', 'description' => 'Has events.']);
        $dates = [
            'owner_id' => $owner->id,
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHour(),
            'join_till' => now()->addWeek(),
        ];
        $community->events()->create(['title' => 'Members Only Picnic', ...$dates]);
        Event::create(['title' => 'Open City Marathon', ...$dates]);

        $this->actingAs($owner)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Open City Marathon')
            ->assertDontSee('Members Only Picnic');

        $this->actingAs($owner)
            ->get(route('events.index', ['my_event' => 1]))
            ->assertOk()
            ->assertSee('Open City Marathon')
            ->assertDontSee('Members Only Picnic');

        // コミュニティのページには出る
        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertSee('Members Only Picnic');
    }

    public function test_community_event_pages_keep_the_community_sidebar(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Sidebar Club', 'description' => 'Has events.']);
        $dates = [
            'owner_id' => $owner->id,
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHour(),
            'join_till' => now()->addWeek(),
        ];
        $communityEvent = $this->withCategory($community->events()->create(['title' => 'Club Picnic', ...$dates]));
        $openEvent = $this->withCategory(Event::create(['title' => 'Open Marathon', ...$dates]));

        foreach (['events.show', 'events.edit', 'events.participants'] as $routeName) {
            $this->actingAs($owner)
                ->get(route($routeName, $communityEvent))
                ->assertOk()
                ->assertSee('community-sidebar', false)
                ->assertSee('community-nav-active', false)
                ->assertDontSee('event-sidebar"', false);
        }

        $this->actingAs($owner)
            ->get(route('events.show', $communityEvent))
            ->assertSee(route('community.show', $community->id), false);

        $this->actingAs($owner)
            ->get(route('events.show', $openEvent))
            ->assertOk()
            ->assertSee('class="event-sidebar"', false)
            ->assertDontSee('<aside class="community-sidebar"', false);
    }

    public function test_create_form_links_to_community_guidelines_and_terms(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('community.create'))
            ->assertOk()
            ->assertSee('data-bs-target="#modal-guide"', false)
            ->assertSee('id="modal-guide"', false)
            ->assertSee('data-bs-target="#modal-term"', false)
            ->assertSee('id="modal-term"', false);
    }

    public function test_post_an_event_opens_the_same_form_as_the_events_create_page(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Form Club', 'description' => 'Has a form.']);
        $createUrl = route('community.events.create', $community->id);

        // 詳細ページにはフォームを置かず、作成ページへのリンクだけ出す
        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertOk()
            ->assertSee($createUrl, false)
            ->assertDontSee('name="start_at"', false);

        $this->actingAs($owner)
            ->get($createUrl)
            ->assertOk()
            ->assertSee('Make your event.')
            ->assertSee('name="category_id"', false)
            ->assertSee('action="' . route('community.events.store', $community->id) . '"', false)
            ->assertSee('<aside class="community-sidebar"', false);

        // 入力エラーのときは作成ページに戻る
        $this->actingAs($owner)
            ->from($createUrl)
            ->post(route('community.events.store', $community->id), ['title' => ''])
            ->assertRedirect($createUrl)
            ->assertSessionHasErrors(['title', 'category_id']);

        $this->actingAs(User::factory()->create())
            ->get($createUrl)
            ->assertForbidden();
    }

    public function test_community_event_form_omits_language_visibility_and_approval(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Short Form Club', 'description' => 'Fewer fields.']);

        $this->actingAs($owner)
            ->get(route('community.events.create', $community->id))
            ->assertOk()
            ->assertDontSee('name="language"', false)
            ->assertDontSee('name="visibility"', false)
            ->assertDontSee('name="approval_required"', false);

        // イベントページの作成には残っている
        $this->actingAs($owner)
            ->get(route('events.create'))
            ->assertOk()
            ->assertSee('name="language"', false)
            ->assertSee('name="visibility"', false)
            ->assertSee('name="approval_required"', false);

        $data = collect($this->eventFormData(['title' => 'Short Form Event']))
            ->except(['language', 'visibility', 'approval_required'])
            ->all();

        // 送られてきても無視して既定値で作る
        $this->actingAs($owner)
            ->post(route('community.events.store', $community->id), [...$data, 'visibility' => 'followers'])
            ->assertRedirect(route('community.show', $community->id));

        $event = Event::where('title', 'Short Form Event')->firstOrFail();
        $this->assertSame($community->id, $event->community_id);
        $this->assertSame('en', $event->language);
        $this->assertSame('public', $event->visibility);
        $this->assertFalse((bool) $event->approval_required);

        // イベントページの作成では今までどおり必須
        $this->actingAs($owner)
            ->post(route('events.store'), $data)
            ->assertSessionHasErrors(['language', 'visibility', 'approval_required']);
    }

    public function test_community_detail_page_links_to_event_details(): void
    {
        $owner = User::factory()->create();
        $community = $owner->communitiesOwned()->create(['name' => 'Linked Club', 'description' => 'Has events.']);
        $event = $this->withCategory($community->events()->create([
            'title' => 'Linked Picnic',
            'owner_id' => $owner->id,
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHour(),
            'join_till' => now()->addWeek(),
        ]));

        // 他のメンバーや未参加の人からも開ける
        $visitor = User::factory()->create();
        $this->actingAs($visitor)
            ->get(route('community.show', $community->id))
            ->assertOk()
            ->assertSee('href="' . route('events.show', $event->id) . '"', false)
            ->assertSee('View details');

        $this->actingAs($visitor)
            ->get(route('events.show', $event->id))
            ->assertOk()
            ->assertSee('Linked Picnic');
    }

    public function test_only_event_owner_can_delete_community_event_from_its_detail_page(): void
    {
        $communityOwner = User::factory()->create();
        $eventOwner = User::factory()->create();
        $community = $communityOwner->communitiesOwned()->create(['name' => 'Delete Club', 'description' => 'Has events.']);
        $event = $this->withCategory($community->events()->create([
            'title' => 'Deletable Picnic',
            'owner_id' => $eventOwner->id,
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHour(),
            'join_till' => now()->addWeek(),
        ]));
        $deleteUrl = route('events.destroy', $event->id);

        $this->actingAs($eventOwner)
            ->get(route('events.show', $event->id))
            ->assertOk()
            ->assertSee('data-bs-target="#deleteEventModal' . $event->id . '"', false)
            ->assertSee('action="' . $deleteUrl . '"', false);

        // コミュニティのオーナーや他の人にはボタンを出さず、直接送っても消せない
        foreach ([$communityOwner, User::factory()->create()] as $user) {
            $this->actingAs($user)
                ->get(route('events.show', $event->id))
                ->assertOk()
                ->assertDontSee('deleteEventModal', false);
            $this->actingAs($user)->delete($deleteUrl)->assertForbidden();
        }
        $this->assertNotSoftDeleted($event);

        $this->actingAs($eventOwner)
            ->delete($deleteUrl)
            ->assertRedirect(route('community.show', $community->id))
            ->assertSessionMissing('success');
        $this->assertSoftDeleted($event);

        $this->actingAs($eventOwner)
            ->get(route('community.show', $community->id))
            ->assertDontSee('Deletable Picnic');
    }

    public function test_events_outside_communities_have_no_delete_button_on_detail_page(): void
    {
        $owner = User::factory()->create();
        $event = $this->withCategory(Event::create([
            'title' => 'Open Picnic',
            'owner_id' => $owner->id,
            'start_at' => now()->addWeek(),
            'end_at' => now()->addWeek()->addHour(),
            'join_till' => now()->addWeek(),
        ]));

        $this->actingAs($owner)
            ->get(route('events.show', $event->id))
            ->assertOk()
            ->assertDontSee('deleteEventModal', false);
    }

    public function test_events_create_page_still_creates_events_outside_communities(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('events.store'), $this->eventFormData(['title' => 'Open Festival']))
            ->assertRedirect();

        $this->assertNull(Event::where('title', 'Open Festival')->firstOrFail()->community_id);
    }

    // 作成フォームではカテゴリーが必須なので、直接作るテスト用のイベントにも付けておく
    private function withCategory(Event $event): Event
    {
        $event->categories()->attach(Category::create(['name' => 'Sample ' . Str::random(6)])->id);

        return $event;
    }

    private function eventFormData(array $overrides = []): array
    {
        $startAt = now()->addDays(3)->startOfHour();

        return [
            'title' => 'Sample Event',
            'category_id' => Category::create(['name' => 'Sample ' . Str::random(6)])->id,
            'price' => 0,
            'start_at' => $startAt->format('Y-m-d\TH:i'),
            'end_at' => $startAt->copy()->addHours(2)->format('Y-m-d\TH:i'),
            'join_till' => $startAt->copy()->subDay()->format('Y-m-d\TH:i'),
            'capacity' => 10,
            'description' => 'Sample description.',
            'location' => 'Sample Hall',
            'language' => 'en',
            'visibility' => 'public',
            'approval_required' => 0,
            ...$overrides,
        ];
    }

    public function test_non_owner_cannot_post_event_to_community(): void
    {
        $community = User::factory()->create()->communitiesOwned()->create([
            'name' => 'Restricted Event Community',
            'description' => 'Only its owner may post events.',
        ]);
        $startAt = now()->addDays(3)->startOfHour();

        $this->actingAs(User::factory()->create())
            ->post(route('community.events.store', $community->id), [
                'title' => 'Unauthorized Event',
                'start_at' => $startAt->format('Y-m-d\TH:i'),
                'end_at' => $startAt->copy()->addHour()->format('Y-m-d\TH:i'),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('events', ['title' => 'Unauthorized Event']);
    }
}
