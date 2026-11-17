<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_private_information_is_visible_only_to_owner_and_can_be_published_independently(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $profile = $owner->profile()->create(['region' => 'Private Sample City', 'country' => 'Private Sample Country',
            'occupation' => 'Private Sample Occupation', 'show_location' => false, 'show_occupation' => false]);
        $url = route('profile.show', $owner->id);
        $this->actingAs($owner)->get($url)->assertOk()->assertSee('Private Sample City, Private Sample Country')
            ->assertSee('Private Sample Occupation')->assertSee('Only you');
        $this->actingAs($viewer)->get($url)->assertOk()->assertDontSee('Private Sample City')
            ->assertDontSee('Private Sample Country')->assertDontSee('Private Sample Occupation')
            ->assertDontSee('class="profile-basic-info"', false);
        $profile->update(['show_occupation' => true]);
        $this->get($url)->assertOk()->assertSee('Private Sample Occupation')->assertDontSee('Private Sample City');
        $profile->update(['show_location' => true, 'show_occupation' => false]);
        $this->get($url)->assertOk()->assertSee('Private Sample City, Private Sample Country')->assertDontSee('Private Sample Occupation');
    }

    public function test_edit_form_saves_and_validates_visibility_without_changing_other_users(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $other->profile()->create(['show_location' => true, 'show_occupation' => true]);
        $data = ['name' => $owner->name, 'username' => $owner->username, 'preferred_language' => 'en',
            'country' => 'Japan', 'region' => 'Tokyo', 'occupation' => 'Designer',
            'show_location' => '0', 'show_occupation' => '0', 'user_id' => $other->id];
        $this->actingAs($owner)->get(route('profile.edit'))->assertOk()->assertSee('Profile visibility')->assertSee('Only me');
        $this->patch(route('profile.profile.update'), $data)->assertSessionHasNoErrors()->assertRedirect();
        $profile = $owner->profile()->firstOrFail();
        $this->assertFalse($profile->show_location);
        $this->assertFalse($profile->show_occupation);
        $this->assertSame('Tokyo', $profile->region);
        $this->assertSame('Designer', $profile->occupation);
        $this->assertTrue($other->profile->show_location);
        $this->patch(route('profile.profile.update'), array_replace($data, ['show_location' => 'everyone', 'show_occupation' => 'secret']))
            ->assertSessionHasErrors(['show_location', 'show_occupation']);
        unset($data['show_location'], $data['show_occupation']);
        $this->patch(route('profile.profile.update'), $data)->assertSessionHasNoErrors();
        $this->assertFalse($profile->fresh()->show_location);
        $this->assertFalse($profile->fresh()->show_occupation);
        $this->patch(route('profile.profile.update'), $data + ['show_location' => '1', 'show_occupation' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($profile->fresh()->show_location);
        $this->assertTrue($profile->fresh()->show_occupation);
    }
}
