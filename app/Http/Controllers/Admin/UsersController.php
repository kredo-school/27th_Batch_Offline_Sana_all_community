<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    //this function will return the view of the users index page
    public function index(Request $request)
    {
        $users = $this->user->withTrashed()->with('profile');

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sorts = [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
        ];
        $sort = $request->query('sort', 'latest');
        [$column, $direction] = $sorts[$sort] ?? $sorts['latest'];

        $all_users = $users->orderBy($column, $direction)->paginate(10)->withQueryString();
        $userIds = $all_users->getCollection()->modelKeys();

        $pageViewStats = DB::table('page_views')
            ->select('user_id', DB::raw('COUNT(*) as page_view_count'))
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $categoryAnalytics = DB::table('category_views')
            ->join('categories', 'category_views.category_id', '=', 'categories.id')
            ->select('category_views.user_id', 'categories.name', DB::raw('COUNT(*) as view_count'))
            ->whereIn('category_views.user_id', $userIds)
            ->groupBy('category_views.user_id', 'categories.id', 'categories.name')
            ->orderByDesc('view_count')
            ->get()
            ->groupBy('user_id');

        return view('admin.admin', compact(
            'all_users',
            'pageViewStats',
            'categoryAnalytics'
        ));
    }

    //this function will deactivate the user
    public function deactivate($id){
        $this->user->destroy($id);
        return redirect()->back();
    }

    //this function will activate user 
    public function activate($id){
        $this->user->onlyTrashed()->findOrFail($id)->restore();
        return redirect()->back();
    }

    public function changeRole(Request $request, $id)
    {
        abort_if((int) $id === (int) $request->user()->getKey(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $this->user->withTrashed()->findOrFail($id)->update($validated);

        return redirect()->back();
    }

     // ユーザー詳細画面：その人のアクティブ状況や好みのカテゴリを表示
    public function show(User $user)
    {
        // このユーザーのカテゴリー閲覧統計（累計）
        $userCategoryStats = DB::table('category_views')
            ->join('categories', 'category_views.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as view_count'))
            ->where('category_views.user_id', $user->id)
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('view_count', 'desc')
            ->get();

        // このユーザーの直近のアクティビティログ（ページビュー履歴など）
        $recentPageViews = DB::table('page_views')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.show', compact('user', 'userCategoryStats', 'recentPageViews'));
    }
}
