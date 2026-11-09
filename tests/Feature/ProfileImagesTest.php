<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileImagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    private function payload(User $user, array $changes = []): array
    {
        return array_replace([
            'name' => $user->name, 'username' => $user->username, 'preferred_language' => 'en',
        ], $changes);
    }

    private function photo(string $name = 'photo.png'): UploadedFile
    {
        $image = imagecreatetruecolor(400, 200);
        imagefilledrectangle($image, 0, 0, 199, 199, imagecolorallocate($image, 255, 0, 0));
        imagefilledrectangle($image, 200, 0, 399, 199, imagecolorallocate($image, 0, 0, 255));
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        imagedestroy($image);

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_upload_saves_the_selected_pixels_and_preserves_originals_for_reediting(): void
    {
        $user = User::factory()->create();
        $crop = ['x' => 100, 'y' => 100, 'zoom' => 2];
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar' => $this->photo(), 'avatar_crop' => $crop,
            'cover_image' => $this->photo('cover.png'), 'cover_image_crop' => $crop,
        ]))->assertSessionHasNoErrors()->assertRedirect(route('profile.show', $user->id));

        $profile = $user->fresh()->profile;
        foreach (['avatar' => [512, 512], 'cover_image' => [1600, 340]] as $field => [$width, $height]) {
            Storage::disk('public')->assertExists([$profile->$field, $profile->{$field.'_original'}]);
            $image = imagecreatefromstring(Storage::disk('public')->get($profile->$field));
            $this->assertSame($width, imagesx($image));
            $this->assertSame($height, imagesy($image));
            $this->assertSame(255, imagecolorat($image, (int) ($width / 2), (int) ($height / 2)) & 0xFFFFFF);
            $this->assertSame(255, imagecolorat($image, $width - 1, $height - 1));
            imagedestroy($image);
            $this->assertEquals($crop, $profile->{$field.'_crop'});
        }
        $this->get(route('profile.edit'))->assertOk()
            ->assertSee(asset('storage/'.$profile->avatar_original))
            ->assertSee('Adjust photo')->assertSee('Remove photo')->assertSee('Undo removal')
            ->assertSee('profile-image-editor.js');
    }

    public function test_existing_photo_can_be_repositioned_without_losing_the_original(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar' => $this->photo(),
        ]))->assertSessionHasNoErrors();
        $before = $user->fresh()->profile;
        $this->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar_crop' => ['x' => 0, 'y' => 50, 'zoom' => 2],
        ]))->assertSessionHasNoErrors();
        $after = $user->fresh()->profile;
        $this->assertSame($before->avatar_original, $after->avatar_original);
        $this->assertNotSame($before->avatar, $after->avatar);
        Storage::disk('public')->assertMissing($before->avatar);
        Storage::disk('public')->assertExists($after->avatar_original);
        $image = imagecreatefromstring(Storage::disk('public')->get($after->avatar));
        $this->assertSame(0xFF0000, imagecolorat($image, 256, 256) & 0xFFFFFF);
        imagedestroy($image);
    }

    public function test_removal_is_applied_only_on_a_valid_save_and_restores_fallback_images(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar' => $this->photo(), 'cover_image' => $this->photo(),
        ]))->assertSessionHasNoErrors();
        $before = $user->fresh()->profile;
        $paths = [$before->avatar, $before->avatar_original, $before->cover_image, $before->cover_image_original];

        $this->get(route('profile.edit'))->assertOk();
        Storage::disk('public')->assertExists($paths);
        $this->patch(route('profile.profile.update'), $this->payload($user, [
            'name' => '', 'remove_avatar' => 1, 'remove_cover_image' => 1,
        ]))->assertSessionHasErrors('name');
        Storage::disk('public')->assertExists($paths);
        $this->assertSame($before->avatar, $user->fresh()->profile->avatar);

        $this->patch(route('profile.profile.update'), $this->payload($user, [
            'remove_avatar' => 1, 'remove_cover_image' => 1,
        ]))->assertSessionHasNoErrors();
        $after = $user->fresh()->profile;
        foreach (['avatar', 'cover_image', 'avatar_original', 'cover_image_original', 'avatar_crop', 'cover_image_crop'] as $field) {
            $this->assertNull($after->$field);
        }
        Storage::disk('public')->assertMissing($paths);
        $this->get(route('profile.show', $user->id))->assertOk()->assertSee(asset('images/onlylogo.jpg'));
    }

    public function test_crop_bounds_and_conflicting_upload_and_removal_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar_crop' => ['x' => -1, 'y' => 101, 'zoom' => 0],
            'cover_image_crop' => ['x' => 50, 'zoom' => 4],
        ]))->assertSessionHasErrors(['avatar_crop.x', 'avatar_crop.y', 'avatar_crop.zoom', 'cover_image_crop.y', 'cover_image_crop.zoom']);
        $this->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar' => $this->photo(), 'remove_avatar' => 1,
        ]))->assertSessionHasErrors('avatar');
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_unchanged_photos_and_other_users_photos_are_preserved(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Storage::disk('public')->put('avatars/owner.png', $this->photo()->get());
        Storage::disk('public')->put('avatars/other.png', $this->photo()->get());
        $user->profile()->create(['avatar' => 'avatars/owner.png']);
        $other->profile()->create(['avatar' => 'avatars/other.png']);
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar_crop' => ['x' => 50, 'y' => 50, 'zoom' => 1],
        ]))->assertSessionHasNoErrors();
        $this->assertSame('avatars/owner.png', $user->fresh()->profile->avatar);
        $this->patch(route('profile.profile.update'), $this->payload($user, [
            'remove_avatar' => 1, 'user_id' => $other->id, 'avatar_original' => 'avatars/other.png',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('avatars/other.png', $other->fresh()->profile->avatar);
        Storage::disk('public')->assertExists('avatars/other.png');
    }

    public function test_legacy_upload_can_be_adjusted_and_retained_as_the_original(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('avatars/legacy.png', $this->photo()->get());
        $user->profile()->create(['avatar' => 'avatars/legacy.png']);
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar_crop' => ['x' => 100, 'y' => 50, 'zoom' => 2],
        ]))->assertSessionHasNoErrors();
        $profile = $user->fresh()->profile;
        $this->assertSame('avatars/legacy.png', $profile->avatar_original);
        Storage::disk('public')->assertExists([$profile->avatar, $profile->avatar_original]);
    }

    public function test_a_failed_adjustment_rolls_back_data_and_cleans_up_new_files(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('avatars/old.png', $this->photo()->get());
        $user->profile()->create(['avatar' => 'avatars/old.png']);
        $profile = $user->fresh()->profile;
        $profile->cover_image = 'https://example.com/cover.jpg';
        $profile->save();
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'name' => 'Should not be saved', 'avatar' => $this->photo(),
            'cover_image_crop' => ['x' => 0, 'y' => 50, 'zoom' => 2],
        ]))->assertSessionHasErrors('cover_image');
        $this->assertSame($user->name, $user->fresh()->name);
        $this->assertSame('avatars/old.png', $user->fresh()->profile->avatar);
        $this->assertSame(['avatars/old.png'], Storage::disk('public')->allFiles());
    }

    public function test_validation_error_after_an_upload_keeps_the_saved_photo_and_restores_its_crop(): void
    {
        $user = User::factory()->create();
        $savedCrop = ['x' => 10, 'y' => 20, 'zoom' => 1.5];
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar' => $this->photo(), 'avatar_crop' => $savedCrop,
        ]))->assertSessionHasNoErrors();
        $before = $user->fresh()->profile;

        $this->from(route('profile.edit'))->patch(route('profile.profile.update'), $this->payload($user, [
            'name' => '', 'avatar' => $this->photo('replacement.png'), 'avatar_upload_pending' => '1',
            'avatar_crop' => ['x' => 90, 'y' => 100, 'zoom' => 3],
        ]))->assertSessionHasErrors('name')->assertRedirect(route('profile.edit'));

        $response = $this->get(route('profile.edit'))->assertOk()->assertSee('data-upload-lost="1"', false);
        $dom = new \DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        foreach ($savedCrop as $key => $value) {
            $this->assertEquals($value, $xpath->query('//input[@name="avatar_crop['.$key.']"]')->item(0)->getAttribute('value'));
        }
        $this->assertSame($before->avatar, $user->fresh()->profile->avatar);
        Storage::disk('public')->assertExists([$before->avatar, $before->avatar_original]);
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_validation_error_keeps_an_existing_photos_unsaved_position_for_preview(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, [
            'avatar' => $this->photo(),
        ]))->assertSessionHasNoErrors();
        $before = $user->fresh()->profile;

        $this->from(route('profile.edit'))->patch(route('profile.profile.update'), $this->payload($user, [
            'name' => '', 'avatar_upload_pending' => '0', 'avatar_crop' => ['x' => 90, 'y' => 100, 'zoom' => 3],
        ]))->assertSessionHasErrors('name');
        $this->get(route('profile.edit'))->assertOk()->assertSee('data-render-crop="1"', false)
            ->assertSee('name="avatar_crop[x]" value="90"', false)
            ->assertSee('name="avatar_crop[zoom]" value="3"', false);
        $this->assertSame($before->avatar, $user->fresh()->profile->avatar);
    }

    public function test_phone_orientation_is_respected_for_new_and_existing_jpeg_photos(): void
    {
        $image = imagecreatefromstring($this->photo()->get());
        ob_start();
        imagejpeg($image, null, 100);
        $jpeg = ob_get_clean();
        imagedestroy($image);
        $exif = "Exif\0\0II".pack('vVv', 42, 8, 1).pack('vvVv', 0x0112, 3, 1, 6)."\0\0".pack('V', 0);
        $jpeg = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);

        foreach ([true, false] as $upload) {
            $user = User::factory()->create();
            $changes = ['avatar_crop' => ['x' => 50, 'y' => 0, 'zoom' => 1]];
            if ($upload) {
                $changes['avatar'] = UploadedFile::fake()->createWithContent('phone.jpg', $jpeg);
            } else {
                Storage::disk('public')->put('avatars/phone.jpg', $jpeg);
                $user->profile()->create(['avatar' => 'avatars/phone.jpg']);
            }
            $this->actingAs($user)->patch(route('profile.profile.update'), $this->payload($user, $changes))
                ->assertSessionHasNoErrors();
            $image = imagecreatefromstring(Storage::disk('public')->get($user->fresh()->profile->avatar));
            $color = imagecolorsforindex($image, imagecolorat($image, 256, 256));
            $this->assertGreaterThan(240, $color['red']);
            $this->assertLessThan(10, $color['blue']);
            imagedestroy($image);
        }
    }
}
