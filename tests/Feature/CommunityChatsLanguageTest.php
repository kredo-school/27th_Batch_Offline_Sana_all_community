<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityChatsLanguageTest extends TestCase
{
    use RefreshDatabase;

    private function ownedCommunity(User $user, array $attributes): Community
    {
        return Community::create(array_merge([
            'owner_id' => $user->id,
            'name' => 'Club',
            'description' => 'About the club',
            'visibility' => 'public',
        ], $attributes));
    }

    public function test_chats_page_shows_category_and_access_in_the_chosen_language(): void
    {
        $user = User::factory()->create(['preferred_language' => 'ja']);
        // 管理画面のカテゴリー（Sports）と以前の固定カテゴリー（Learning）のどちらも訳す
        $this->ownedCommunity($user, ['name' => 'Ball club', 'category' => 'Sports', 'visibility' => 'approval']);
        $this->ownedCommunity($user, ['name' => 'Study club', 'category' => 'Learning', 'visibility' => 'private']);

        $this->actingAs($user)
            ->get(route('community.chats'))
            ->assertOk()
            ->assertSee('カテゴリー:</strong> スポーツ', false)
            ->assertSee('カテゴリー:</strong> 学習', false)
            ->assertSee('参加方法:</strong> 承認制', false)
            ->assertSee('参加方法:</strong> 招待のみ', false)
            ->assertDontSee('Approval');
    }

    public function test_chats_page_hides_an_empty_category(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);
        $this->ownedCommunity($user, ['category' => null]);

        $this->actingAs($user)
            ->get(route('community.chats'))
            ->assertOk()
            ->assertDontSee('Category:</strong>', false)
            ->assertSee('Access:</strong> Public', false);
    }
}
