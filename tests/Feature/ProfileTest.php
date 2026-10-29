<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

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
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists([$user->profile->avatar, $user->profile->cover_image]);
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
            ->assertDontSee('Public draft event');

        $this->actingAs($owner)->get(route('profile.show', $owner->id))
            ->assertOk()
            ->assertSee('Public published event')
            ->assertSee('Private published event')
            ->assertSee('Public draft event');
    }

    public function test_navbar_uses_storage_url_for_uploaded_avatar(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['avatar' => 'avatars/navbar.jpg']);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('src="'.asset('storage/avatars/navbar.jpg').'" alt="'.$user->name.'"', false);
    }

}
