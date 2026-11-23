<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Category;
use App\Models\Community;
use App\Models\SiteSetting;
use App\Support\Search;

class HomeController extends Controller
{

    private $users;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->users = new User();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // app/Http/Controllers/HomeController.php
    public function index(Request $request)
    {
        $categories = \App\Models\Category::with([
            'events',
            'translations' => fn ($query) => $query
                ->where('field', 'name')
                ->where('target_language', app()->getLocale()),
        ])->get();

        return view('home', [
            'categories' => $categories,
            'homeBackground' => SiteSetting::get('home_background'),
        ]);
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('search'));
        if ($keyword === '') {
            return view('search.results', [
                'keyword' => '',
                'users' => collect(),
                'communities' => collect(),
                'categories' => collect(),
                'events' => collect(),
            ]);
        }

        $keyword = mb_substr($keyword, 0, Search::MAX_LENGTH);

        // どの検索もサイト共通のルール（App\Support\Search）で探す
        $users = User::query()
            ->search($keyword)
            ->with('profile')
            ->orderBy('name')
            ->limit(12)
            ->get();

        // 招待のみのコミュニティは、関係ない人の検索結果には出さない
        $communities = Community::query()
            ->visibleTo($request->user())
            ->search($keyword)
            ->with('owner')
            ->latest()
            ->limit(12)
            ->get();

        // カテゴリーは、どの言語の名前で探しても見つかる
        $categories = Category::query()
            ->whereIn('id', Search::categoryIds($keyword))
            ->orderBy('name')
            ->limit(12)
            ->get();

        // 他の人の下書きや非公開のイベントは出さない（自分のものは出す）
        $events = Event::query()
            ->listedFor($request->user())
            ->whereNull('community_id')
            ->where('end_at', '>=', now())
            ->search($keyword)
            ->with(['owner', 'categories'])
            ->orderBy('start_at')
            ->limit(12)
            ->get();

        return view('search.results', compact('keyword', 'users', 'communities', 'categories', 'events'));
    }
}
