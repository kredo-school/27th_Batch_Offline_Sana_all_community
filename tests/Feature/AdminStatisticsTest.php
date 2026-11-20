<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_sections_preview_ten_rows_and_link_to_more(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        for ($index = 1; $index <= 25; $index++) {
            $user = User::factory()->create(['role' => 'user']);
            $category = Category::create(['name' => 'Category '.$index]);
            $createdAt = now()->subMinutes($index)->toDateTimeString();

            DB::table('category_views')->insert([
                'user_id' => $user->id,
                'session_id' => 'session-'.$index,
                'category_id' => $category->id,
                'created_at' => $createdAt,
            ]);
            DB::table('page_views')->insert([
                'user_id' => $user->id,
                'ip_address' => '192.0.2.'.$index,
                'url' => '/page/'.$index,
                'category_id' => $category->id,
                'created_at' => $createdAt,
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.statistics'))
            ->assertOk();

        $this->assertCount(10, $response->viewData('topCategories'));
        $this->assertCount(10, $response->viewData('topPages'));
        $this->assertCount(10, $response->viewData('activeUsers'));
        $this->assertCount(10, $response->viewData('recentPageViews'));
        foreach (['categories', 'pages', 'active-users', 'recent-page-views'] as $section) {
            $this->assertTrue($response->viewData('hasMore')[$section]);
        }
        $this->assertSame(4, substr_count($response->getContent(), __('Show more')));

        $detailsResponse = $this->get(route('admin.statistics', ['detail' => 'categories']))
            ->assertOk()
            ->assertSee(__('Back'));

        $categories = $detailsResponse->viewData('topCategories');
        $this->assertSame(25, $categories->total());
        $this->assertSame(20, $categories->perPage());
        $this->assertCount(20, $categories);
        $detailsResponse->assertSee('page=2', false);
    }
}
