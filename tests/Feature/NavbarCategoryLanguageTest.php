<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarCategoryLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_categories_follow_the_chosen_language_even_when_not_in_the_database(): void
    {
        // データベースには小文字の sports だけがある状態（Events などは未登録）
        $sports = Category::create(['name' => 'sports']);
        $user = User::factory()->create(['preferred_language' => 'ja']);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSeeInOrder(['イベント', 'ミートアップ', 'スポーツ', '交流', '求人'])
            // 大文字小文字が違っても、登録済みのカテゴリーとして ID で絞り込む
            ->assertSee('href="' . route('events.index', ['category' => $sports->id]) . '"', false);
    }

    public function test_english_shows_the_configured_spelling(): void
    {
        Category::create(['name' => 'sports']);
        $user = User::factory()->create(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertSeeInOrder(['Events', 'Meet up', 'Sports', 'Exchange', 'Jobs']);
    }
}
