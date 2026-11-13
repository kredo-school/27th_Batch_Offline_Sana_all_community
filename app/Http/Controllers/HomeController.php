<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Category;
use App\Models\Community;
use App\Models\SiteSetting;

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

        $like = "%{$keyword}%";

        $users = User::query()
            ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('username', 'like', $like))
            ->with('profile')
            ->orderBy('name')
            ->limit(12)
            ->get();

        $communities = Community::query()
            ->where(fn ($query) => $query->where('name', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('category', 'like', $like))
            ->with('owner')
            ->latest()
            ->limit(12)
            ->get();

        $categories = Category::query()
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit(12)
            ->get();

        $events = Event::query()
            ->whereNull('community_id')
            ->where('end_at', '>=', now())
            ->where(fn ($query) => $query->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('location', 'like', $like)
                ->orWhereHas('owner', fn ($owner) => $owner
                    ->where('name', 'like', $like)
                    ->orWhere('username', 'like', $like))
                ->orWhereHas('categories', fn ($category) => $category->where('name', 'like', $like)))
            ->with(['owner', 'categories'])
            ->orderBy('start_at')
            ->limit(12)
            ->get();

        return view('search.results', compact('keyword', 'users', 'communities', 'categories', 'events'));
    }
}
