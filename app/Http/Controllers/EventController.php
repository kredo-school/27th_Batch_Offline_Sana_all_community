<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventParticipant;

class EventController extends Controller
{
    public function index()
    {
        return 'events index';
    }

    public function show(Event $event)
    {
        $event->load(['owner', 'categories', 'comments.user']);

        $myParticipation = null;
        if (auth()->check()) {
            $myParticipation = EventParticipant::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('events.detail', compact('event', 'myParticipation'));
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
        $comment->user_id = Auth::id();
        $comment->content = $request->content;
        $comment->save();

        return redirect()->route('events.show', $event);
    }

    public function requestJoin(Event $event)
    {
        $alreadyRequested = EventParticipant::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->exists();
        if ($alreadyRequested) {
            return back()->with('error', 'You have already requested to join this event.');
        }

        if (now()->greaterThan($event->join_till)) {
            return back()->with('error', 'Registration for this event is closed.');
        }

        $joinedCount = EventParticipant::where('event_id', $event->id)
            ->where('status', 'approved')
            ->count();
        if ($event->capacity && $joinedCount >= $event->capacity) {
            return back()->with('error', 'This event is full.');
        }

        $participant = new EventParticipant();
        $participant->event_id = $event->id;
        $participant->user_id = auth()->id();
        $participant->status = $event->approval_required ? 'pending' : 'approved';
        $participant->requested_at = now();
        if (! $event->approval_required) {
            $participant->responded_at = now();
        }
        $participant->save();

        $message = $event->approval_required
            ? 'Your request has been sent. Please wait for the host to approve it.'
            : 'You have joined this event!';

        return redirect()->route('events.show', $event)->with('success', $message);
    }

    public function cancelJoin(Event $event)
    {
        EventParticipant::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->delete();

        return redirect()->route('events.show', $event)->with('success', 'Your request has been cancelled.');
    }
}