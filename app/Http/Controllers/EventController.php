<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Event;
use App\Models\EventCategory;

class EventController extends Controller
{
    public function index()
    {
        return 'events index';
    }

    public function show(Event $event)
    {
        $event->load(['owner', 'categories', 'comments.user']);

        return view('events.detail', compact('event'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:60',
            'category_id'       => 'required|exists:categories,id',
            'price'             => 'required|numeric|min:0',
            'start_at'          => 'required|date',
            'end_at'            => 'required|date|after_or_equal:start_at',
            'join_till'         => 'required|date|before_or_equal:start_at',
            'capacity'          => 'required|integer|min:1',
            'description'       => 'required|string|max:500',
            'location'          => 'required|string|max:60',
            'image'             => 'nullable|image|max:2048',
            'language'          => 'required|in:en,ja',
            'visibility'        => 'required|in:public,members,followers',
            'approval_required' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $validated['owner_id'] = auth()->id();

        $categoryId = $validated['category_id'];
        unset($validated['category_id']);

        $event = Event::create($validated);
        $event->categories()->attach($categoryId);

        return redirect()->route('events.show', $event);
    }

    public function edit(Event $event)
    {
        $categories = Category::all();
        $selectedCategoryId = EventCategory::where('event_id', $event->id)->value('category_id');

        return view('events.edit', compact('event', 'categories', 'selectedCategoryId'));
    }

    public function update(Request $request, Event $event)
    {
        return redirect()->route('events.edit', $event);
    }

    public function storeComment(Request $request, Event $event)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = new Comment();
        $comment->event_id = $event->id;
        $comment->user_id = auth()->id();
        $comment->content = $request->content;
        $comment->save();

        return redirect()->route('events.show', $event);
    }
}