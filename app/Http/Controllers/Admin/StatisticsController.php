<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $totalPageViews = DB::table('page_views')->count();
        $guestPageViews = DB::table('page_views')->whereNull('user_id')->count();
        $uniquePageViewUsers = DB::table('page_views')->whereNotNull('user_id')->distinct()->count('user_id');
        $totalCategoryViews = DB::table('category_views')->count();
        $trackedCategories = DB::table('category_views')->distinct()->count('category_id');

        $periodOptions = [
            'week' => 'Week',
            'month' => 'Month',
            'year' => 'Year',
            'three-years' => '3 years',
        ];
        $requestedPeriod = $request->query('period', 'week');
        $chartPeriod = is_string($requestedPeriod) && array_key_exists($requestedPeriod, $periodOptions)
            ? $requestedPeriod
            : 'week';
        $monthlyBuckets = in_array($chartPeriod, ['year', 'three-years'], true);
        $bucketCount = match ($chartPeriod) {
            'week' => 7,
            'month' => 30,
            'year' => 12,
            default => 36,
        };
        $chartStart = $monthlyBuckets
            ? today()->startOfMonth()->subMonths($bucketCount - 1)
            : today()->subDays($bucketCount - 1)->startOfDay();
        $chartEnd = now();
        $bucketExpression = $monthlyBuckets ? 'SUBSTR(created_at, 1, 7)' : 'DATE(created_at)';
        $chartBuckets = collect(range(0, $bucketCount - 1))->map(function (int $offset) use ($chartStart, $monthlyBuckets) {
            $date = $chartStart->copy();

            if ($monthlyBuckets) {
                $date->addMonths($offset);
            } else {
                $date->addDays($offset);
            }

            return (object) [
                'key' => $date->format($monthlyBuckets ? 'Y-m' : 'Y-m-d'),
                'label' => \App\Support\LocalizedValue::dateTime(
                    $date,
                    $monthlyBuckets ? 'month-year-short' : 'date-short',
                ),
                'title' => \App\Support\LocalizedValue::dateTime(
                    $date,
                    $monthlyBuckets ? 'month-year' : 'date',
                ),
            ];
        });
        $seriesFromCounts = function ($counts) use ($chartBuckets) {
            return $chartBuckets->map(function ($bucket) use ($counts) {
                return (object) [
                    'key' => $bucket->key,
                    'label' => $bucket->label,
                    'title' => $bucket->title,
                    'count' => (int) $counts->get($bucket->key, 0),
                ];
            });
        };

        $detail = $request->query('detail');
        $detailSections = ['categories', 'pages', 'active-users', 'recent-page-views'];
        $detail = in_array($detail, $detailSections, true) ? $detail : null;
        $hasMore = [];
        $loadSection = function ($query, string $section) use ($detail, &$hasMore) {
            if ($detail === $section) {
                $hasMore[$section] = false;

                return $query->paginate(20)->withQueryString();
            }

            $preview = $query->limit(11)->get();
            $hasMore[$section] = $preview->count() > 10;

            return $preview->take(10);
        };

        $topPagesQuery = DB::table('page_views')
            ->select('url', DB::raw('COUNT(*) as view_count'), DB::raw('MAX(created_at) as last_viewed_at'))
            ->groupBy('url')
            ->orderByDesc('view_count');

        $topPages = $loadSection($topPagesQuery, 'pages');

        $topCategoriesQuery = DB::table('category_views')
            ->join('categories', 'category_views.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(*) as view_count'),
                DB::raw('COUNT(DISTINCT category_views.user_id) as unique_users')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('view_count');

        $topCategories = $loadSection($topCategoriesQuery, 'categories');

        $loggedInUserCounts = DB::table('page_views')
            ->selectRaw("$bucketExpression as period, COUNT(DISTINCT user_id) as metric_count")
            ->whereNotNull('user_id')
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupByRaw($bucketExpression)
            ->pluck('metric_count', 'period');
        $loggedInUserSeries = $seriesFromCounts($loggedInUserCounts);

        $newUserCounts = DB::table('users')
            ->selectRaw("$bucketExpression as period, COUNT(*) as metric_count")
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupByRaw($bucketExpression)
            ->pluck('metric_count', 'period');
        $newUserSeries = $seriesFromCounts($newUserCounts);

        $makeLineChart = function (string $title, $series) {
            $maxCount = max(1, (int) $series->max('count'));
            $lastIndex = max(1, $series->count() - 1);
            $points = $series->values()->map(function ($bucket, int $index) use ($maxCount, $lastIndex) {
                return (object) [
                    'x' => number_format(36 + ($index / $lastIndex) * 674, 2, '.', ''),
                    'y' => number_format(150 - (($bucket->count / $maxCount) * 120), 2, '.', ''),
                    'title' => $bucket->title,
                    'count' => $bucket->count,
                ];
            });

            return (object) [
                'title' => $title,
                'series' => $series,
                'maxCount' => $maxCount,
                'points' => $points,
                'path' => $points->map(function ($point) {
                    return $point->x.','.$point->y;
                })->implode(' '),
            ];
        };
        $lineCharts = [
            $makeLineChart('Logged-in page viewers', $loggedInUserSeries),
            $makeLineChart('New users', $newUserSeries),
        ];

        $topCategoryChart = DB::table('category_views')
            ->join('categories', 'category_views.category_id', '=', 'categories.id')
            ->select('categories.id', 'categories.name', DB::raw('COUNT(*) as view_count'))
            ->whereBetween('category_views.created_at', [$chartStart, $chartEnd])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('view_count')
            ->orderBy('categories.name')
            ->limit(10)
            ->get();
        $maxCategoryChartCount = max(1, (int) $topCategoryChart->max('view_count'));

        $usersByPreferredLanguage = DB::table('users')
            ->select('preferred_language', DB::raw('COUNT(*) as user_count'))
            ->whereNull('deleted_at')
            ->groupBy('preferred_language')
            ->orderByDesc('user_count')
            ->orderBy('preferred_language')
            ->get();
        $maxPreferredLanguageCount = max(1, (int) $usersByPreferredLanguage->max('user_count'));

        $pageActivity = DB::table('page_views')
            ->select('user_id', DB::raw('COUNT(*) as page_view_count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $categoryActivity = DB::table('category_views')
            ->select('user_id', DB::raw('COUNT(*) as category_view_count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $activeUsersQuery = DB::table('users')
            ->leftJoinSub($pageActivity, 'page_activity', function ($join) {
                $join->on('users.id', '=', 'page_activity.user_id');
            })
            ->leftJoinSub($categoryActivity, 'category_activity', function ($join) {
                $join->on('users.id', '=', 'category_activity.user_id');
            })
            ->where(function ($query) {
                $query->where('page_activity.page_view_count', '>', 0)
                    ->orWhere('category_activity.category_view_count', '>', 0);
            })
            ->select(
                'users.id',
                'users.username',
                'users.email',
                DB::raw('COALESCE(page_activity.page_view_count, 0) as page_view_count'),
                DB::raw('COALESCE(category_activity.category_view_count, 0) as category_view_count')
            )
            ->orderByRaw('COALESCE(page_activity.page_view_count, 0) + COALESCE(category_activity.category_view_count, 0) DESC');

        $activeUsers = $loadSection($activeUsersQuery, 'active-users');

        $recentPageViewsQuery = DB::table('page_views')
            ->leftJoin('users', 'page_views.user_id', '=', 'users.id')
            ->select('page_views.url', 'page_views.ip_address', 'page_views.created_at', 'users.username as user_name')
            ->orderByDesc('page_views.created_at');

        $recentPageViews = $loadSection($recentPageViewsQuery, 'recent-page-views');

        return view('admin.statistics.index', compact(
            'totalPageViews',
            'guestPageViews',
            'uniquePageViewUsers',
            'totalCategoryViews',
            'trackedCategories',
            'periodOptions',
            'chartPeriod',
            'loggedInUserSeries',
            'newUserSeries',
            'lineCharts',
            'topCategoryChart',
            'maxCategoryChartCount',
            'usersByPreferredLanguage',
            'maxPreferredLanguageCount',
            'detail',
            'hasMore',
            'topPages',
            'topCategories',
            'activeUsers',
            'recentPageViews'
        ));
    }
}
