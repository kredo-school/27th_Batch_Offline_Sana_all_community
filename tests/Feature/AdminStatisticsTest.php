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

    public function test_statistics_charts_show_activity_signups_and_category_views_for_selected_periods(): void
    {
        $adminCreatedAt = now()->subYears(4)->toDateTimeString();
        $admin = User::factory()->create([
            'role' => 'admin',
            'created_at' => $adminCreatedAt,
            'updated_at' => $adminCreatedAt,
        ]);
        $recentCreatedAt = now()->subDays(2)->setTime(12, 0)->toDateTimeString();
        $recentUser = User::factory()->create([
            'preferred_language' => 'ja',
            'created_at' => $recentCreatedAt,
            'updated_at' => $recentCreatedAt,
        ]);
        $olderCreatedAt = now()->subMonths(2)->setTime(12, 0)->toDateTimeString();
        $olderUser = User::factory()->create([
            'preferred_language' => 'en',
            'created_at' => $olderCreatedAt,
            'updated_at' => $olderCreatedAt,
        ]);

        $recentCategory = Category::create(['name' => 'Recent category']);
        $olderCategory = Category::create(['name' => 'Older category']);

        DB::table('page_views')->insert([
            ['user_id' => $recentUser->id, 'ip_address' => '192.0.2.1', 'url' => '/recent/1', 'created_at' => $recentCreatedAt],
            ['user_id' => $recentUser->id, 'ip_address' => '192.0.2.1', 'url' => '/recent/2', 'created_at' => $recentCreatedAt],
            ['user_id' => $olderUser->id, 'ip_address' => '192.0.2.2', 'url' => '/older', 'created_at' => $olderCreatedAt],
        ]);
        DB::table('category_views')->insert([
            ['user_id' => $recentUser->id, 'session_id' => 'recent', 'category_id' => $recentCategory->id, 'created_at' => $recentCreatedAt],
            ['user_id' => $recentUser->id, 'session_id' => 'recent', 'category_id' => $recentCategory->id, 'created_at' => $recentCreatedAt],
            ['user_id' => $olderUser->id, 'session_id' => 'older', 'category_id' => $olderCategory->id, 'created_at' => $olderCreatedAt],
        ]);

        $weekResponse = $this->actingAs($admin)
            ->get(route('admin.statistics', ['period' => 'week']))
            ->assertOk();

        $weekLoggedInUsers = $weekResponse->viewData('loggedInUserSeries');
        $weekNewUsers = $weekResponse->viewData('newUserSeries');
        $weekLineCharts = $weekResponse->viewData('lineCharts');
        $recentDay = now()->subDays(2)->toDateString();
        $preferredLanguages = $weekResponse->viewData('usersByPreferredLanguage');
        $this->assertCount(7, $weekLoggedInUsers);
        $this->assertSame(1, $weekLoggedInUsers->firstWhere('key', $recentDay)->count);
        $this->assertSame(1, $weekNewUsers->firstWhere('key', $recentDay)->count);
        $this->assertCount(7, $weekLineCharts[0]->points);
        $this->assertCount(7, $weekLineCharts[1]->points);
        $this->assertSame(
            ['en', 'ja'],
            $preferredLanguages->pluck('preferred_language')->all()
        );
        $this->assertSame([2, 1], $preferredLanguages->pluck('user_count')->map(fn ($count) => (int) $count)->all());
        $this->assertSame(
            ['Recent category'],
            $weekResponse->viewData('topCategoryChart')->pluck('name')->all()
        );

        $monthResponse = $this->get(route('admin.statistics', ['period' => 'month']))->assertOk();
        $this->assertCount(30, $monthResponse->viewData('newUserSeries'));
        $this->assertCount(30, $monthResponse->viewData('lineCharts')[0]->points);
        $this->assertSame(
            ['Recent category'],
            $monthResponse->viewData('topCategoryChart')->pluck('name')->all()
        );

        $yearResponse = $this->get(route('admin.statistics', ['period' => 'year']))->assertOk();
        $this->assertCount(12, $yearResponse->viewData('newUserSeries'));
        $this->assertCount(12, $yearResponse->viewData('lineCharts')[0]->points);
        $this->assertSame(
            ['Recent category', 'Older category'],
            $yearResponse->viewData('topCategoryChart')->pluck('name')->all()
        );
        $this->assertSame(2, $yearResponse->viewData('newUserSeries')->sum('count'));

        $threeYearsResponse = $this->get(route('admin.statistics', ['period' => 'three-years']))->assertOk();
        $this->assertCount(36, $threeYearsResponse->viewData('newUserSeries'));
        $this->assertCount(36, $threeYearsResponse->viewData('lineCharts')[0]->points);
        $this->assertSame(
            ['Recent category', 'Older category'],
            $threeYearsResponse->viewData('topCategoryChart')->pluck('name')->all()
        );
        $this->assertSame(2, $threeYearsResponse->viewData('newUserSeries')->sum('count'));
    }
}
