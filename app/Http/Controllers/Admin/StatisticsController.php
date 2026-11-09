<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $totalPageViews = DB::table('page_views')->count();
        $guestPageViews = DB::table('page_views')->whereNull('user_id')->count();
        $uniquePageViewUsers = DB::table('page_views')->whereNotNull('user_id')->distinct()->count('user_id');
        $totalCategoryViews = DB::table('category_views')->count();
        $trackedCategories = DB::table('category_views')->distinct()->count('category_id');

        $topPages = DB::table('page_views')
            ->select('url', DB::raw('COUNT(*) as view_count'), DB::raw('MAX(created_at) as last_viewed_at'))
            ->groupBy('url')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        $topCategories = DB::table('category_views')
            ->join('categories', 'category_views.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('COUNT(*) as view_count'),
                DB::raw('COUNT(DISTINCT category_views.user_id) as unique_users')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        $pageActivity = DB::table('page_views')
            ->select('user_id', DB::raw('COUNT(*) as page_view_count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $categoryActivity = DB::table('category_views')
            ->select('user_id', DB::raw('COUNT(*) as category_view_count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id');

        $activeUsers = DB::table('users')
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
                'users.name',
                'users.email',
                DB::raw('COALESCE(page_activity.page_view_count, 0) as page_view_count'),
                DB::raw('COALESCE(category_activity.category_view_count, 0) as category_view_count')
            )
            ->orderByRaw('COALESCE(page_activity.page_view_count, 0) + COALESCE(category_activity.category_view_count, 0) DESC')
            ->limit(10)
            ->get();

        $dailyCategoryCounts = DB::table('category_views')
            ->selectRaw('DATE(created_at) as view_date, COUNT(*) as view_count')
            ->where('created_at', '>=', today()->subDays(6)->startOfDay())
            ->groupByRaw('DATE(created_at)')
            ->orderBy('view_date')
            ->pluck('view_count', 'view_date');

        $dailyCategoryViews = collect(range(0, 6))->map(function ($offset) use ($dailyCategoryCounts) {
            $date = today()->subDays(6 - $offset)->toDateString();

            return (object) [
                'date' => $date,
                'view_count' => (int) $dailyCategoryCounts->get($date, 0),
            ];
        });
        $maxDailyCategoryViews = max(1, (int) $dailyCategoryViews->max('view_count'));

        $recentPageViews = DB::table('page_views')
            ->leftJoin('users', 'page_views.user_id', '=', 'users.id')
            ->select('page_views.url', 'page_views.ip_address', 'page_views.created_at', 'users.name as user_name')
            ->orderByDesc('page_views.created_at')
            ->limit(20)
            ->get();

        return view('admin.statistics.index', compact(
            'totalPageViews',
            'guestPageViews',
            'uniquePageViewUsers',
            'totalCategoryViews',
            'trackedCategories',
            'topPages',
            'topCategories',
            'activeUsers',
            'dailyCategoryViews',
            'maxDailyCategoryViews',
            'recentPageViews'
        ));
    }
}
