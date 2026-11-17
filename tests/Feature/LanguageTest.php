<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_preferred_language(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->post(route('language.update'), ['locale' => 'ja'])
            ->assertRedirect();

        $this->assertSame('ja', $user->fresh()->preferred_language);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk();

        $this->assertSame('ja', App::getLocale());
    }

    public function test_language_switch_rejects_unsupported_locale(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('language.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');

        $this->assertSame('en', $user->fresh()->preferred_language);
    }

    public function test_user_can_select_each_supported_locale(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            $this->actingAs($user)
                ->post(route('language.update'), ['locale' => $locale])
                ->assertRedirect();

            $this->assertSame($locale, $user->fresh()->preferred_language);

            $this->actingAs($user)
                ->get(route('home'))
                ->assertOk()
                ->assertSee('Sana ALL');

            $this->assertSame($locale, App::getLocale());
        }
    }

    public function test_guest_pages_render_translated_auth_labels(): void
    {
        foreach (array_keys(config('app.supported_locales')) as $locale) {
            $this->withSession(['locale' => $locale])
                ->get(route('login'))
                ->assertOk()
                ->assertSee(__('Login', [], $locale));
        }
    }

    public function test_category_labels_follow_the_selected_language(): void
    {
        $user = User::factory()->create(['preferred_language' => 'ja']);
        $category = Category::create(['name' => 'Events']);
        Category::create(['name' => 'Jobs']);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('イベント')
            ->assertSee('求人')
            ->assertSee(route('events.index', ['category' => $category->id]), false);

        $user->update(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Events');
    }
}