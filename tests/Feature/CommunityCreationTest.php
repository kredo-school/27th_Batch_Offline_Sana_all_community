<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $startAt = now()->addDays(3)->startOfHour();
        $endAt = $startAt->copy()->addHours(2);

        $this->actingAs($owner)
            ->post(route('community.events.store', $community->id), [
                'title' => 'Community Workshop',
                'description' => 'A hands-on workshop.',
                'location' => 'Community Hall',
                'start_at' => $startAt->format('Y-m-d\TH:i'),
                'end_at' => $endAt->format('Y-m-d\TH:i'),
            ])
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

    public function test_create_shows_success_message(): void
    {
        $this->actingAs(User::factory()->create())
            ->followingRedirects()
            ->post(route('community.store'), [
                'name' => 'Flash Community',
                'category' => 'Sports',
                'visibility' => 'public',
                'description' => 'Checks the flash message.',
            ])
            ->assertOk()
            ->assertSee('コミュニティを作成しました。');
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

        $this->actingAs($owner)
            ->get(route('community.show', $community->id))
            ->assertOk()
            ->assertSee('id="members"', false)
            ->assertSee('Owner Person')
            ->assertSee('Approved Person')
            ->assertDontSee('Pending Person');
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
