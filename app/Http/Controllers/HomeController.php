<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;

class HomeController extends Controller
{

    private $users;
    private $events;
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
    public function index()
    {
        $categories = \App\Models\Category::with('events')->get();

        return view('home', compact('categories'));
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('search'));
        $type = $request->input('type', 'events');

        if ($type === 'user') {
            $users = $this->users->where('name', 'like', "%{$keyword}%")->get();
            return view('user.search', compact('users', 'keyword'))
                ->with('search', $keyword);
        }

        if ($type === 'events') {
            $events = $this->events->where('name', 'like', "%{$keyword}%")
                ->when($request->filled('category'), function ($query) use ($request) {
                    $query->where('category_id', $request->input('category'));
                })
                ->orderBy('id')
                ->paginate(12)
                ->withQueryString();

            return view('events.search', compact('events', 'keyword'))
                ->with('search', $keyword);
        }

        // if ($type === 'order') {
        //     $categories = $this->orders->where('name', 'like', "%{$keyword}%")->get();
        //     $orders = $this->orders->with(['order', 'order.product', 'likes'])
        //         ->whereHas('order.product', function ($categoryQuery) use ($keyword) {
        //             $categoryQuery->where('name', 'like', "%{$keyword}%");
        //         })
        //         ->latest()
        //         ->get();

        //     return view('order.search', compact('orders', 'posts', 'keyword'))
        //         ->with('search', $keyword);
        // }

        // return redirect()->route('index');
    }
}
