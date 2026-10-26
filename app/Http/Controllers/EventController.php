<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventParticipant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();


        // search keywords
        $query->when($request->filled('keyword'), function ($query) use ($request) {
            $keyword = $request->input('keyword');

            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        });

        // search categories
        $query->when(
            $request->filled('category') && $request->input('category') !== 'all',
            function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            }
        );

        // sort
        match ($request->input('sort', 'date_asc')) {
            'date_desc' => $query->orderByDesc('start_at'),
            'created_desc' => $query->latest(),
            default => $query->orderBy('start_at'),
        };

        // get month and year that be displayed
        $displayMonth = $request->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($displayMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($displayMonth)->endOfMonth();

        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $calendarEvents = Event::where(function ($q) use ($startDate, $endDate) {
            $q->where('start_at', '<=', $endDate)->where('end_at', '>=', $startDate);
        })->orWhere(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('join_till', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        })->get();

        // イベント日（start_at 〜 end_at）に該当する日付をすべて抽出してフラグ用配列を作る
        $eventDatesMap = [];
        foreach ($calendarEvents as $event) {
            $eventStart = Carbon::parse($event->start_at)->startOfDay();
            $eventEnd = Carbon::parse($event->end_at)->endOfDay();

            // イベント期間の1日ずつを生成してマップに登録
            $eventPeriod = CarbonPeriod::create($eventStart, $eventEnd);
            foreach ($eventPeriod as $date) {
                $eventDatesMap[$date->format('Y-m-d')] = true;
            }
        }

        // 予約受付終了日のマップを作る
        $deadlinesMap = [];
        foreach ($calendarEvents as $event) {
            if ($event->join_till) {
                $deadlineDate = Carbon::parse($event->join_till)->format('Y-m-d');
                $deadlinesMap[$deadlineDate] = true;
            }
        }

        // カレンダーの全マス目（1日ずつ）に対して判定を行う
        $period = CarbonPeriod::create($startDate, $endDate);
        $calendarDays = [];

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $calendarDays[] = [
                'date' => $dateString,
                'is_other_month' => $date->month !== $startOfMonth->month,
                'is_event_date' => isset($eventDatesMap[$dateString]),  // 期間内なら true
                'is_closed_date' => isset($deadlinesMap[$dateString]),  // 締切日なら true
            ];
        }

        // Event
        $events = $query
            ->with(['categories'])
            ->paginate(10)
            ->through(function ($event) {
                $event->category_name = ucfirst($event->category);
                $event->status_label = $event->start_at->isToday()
                    ? 'Today'
                    : $event->start_at->diffForHumans();

                $event->status_color = 'blue';
                $event->is_bookmarked = false;

                return $event;
            });

        return view('events.index', array_merge(
        ['events' => $events],
        $this->calendarData()
    ));
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

        return view('events.detail', array_merge(compact('event', 'myParticipation'), $this->calendarData()));
    }

    public function create()
    {
        $categories = Category::all();

        return view('events.create', array_merge(compact('categories'), $this->calendarData()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:60',
            'category_id'       => 'array|exists:categories,id',
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

        if ($request->category_id) {
            $event_categories = [];
            foreach ($request->category_id as $category_id) {
                $event_categories[] = ['category_id' => $category_id];
            }

            $request->eventCategory()->creatMany($event_categories);
        }

        $validated['owner_id'] = Auth::id();

        $categoryId = $validated['category_id'];
        unset($validated['category_id']);

        $event = Event::create($validated);
        $event->categories()->attach($categoryId);

        return redirect()->route('events.show', $event);
    }

    public function edit(Event $event)
    {
        if ($event->owner_id !== auth()->id()) {
            abort(403);
        }

        $categories = Category::all();
        $selectedCategoryId = EventCategory::where('event_id', $event->id)->value('category_id');

        return view('events.edit', array_merge(compact('event', 'categories', 'selectedCategoryId'), $this->calendarData()));
    }

    public function update(Request $request, Event $event)
    {
        // 主催者以外は保存できない
        if ($event->owner_id !== auth()->id()) {
            abort(403);
        }

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
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $categoryId = $validated['category_id'];
        unset($validated['category_id']);

        $event->update($validated);
        $event->categories()->sync([$categoryId]);

        return redirect()->route('events.show', $event)->with('success', 'Your event has been updated.');
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
        if ($event->status === 'cancelled') {
            return back()->with('error', 'This event has been cancelled.');
        }
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

    public function participants(Event $event)
    {
        $isOwner = $event->owner_id === auth()->id();
        $participants = EventParticipant::with('user')
            ->where('event_id', $event->id)
            ->when(! $isOwner, fn ($query) => $query->where('status', 'approved'))
            ->orderBy('requested_at')
            ->get();

        return view('events.participants', array_merge(compact('event', 'participants', 'isOwner'), $this->calendarData()));
    }

    public function approveParticipant(Event $event, EventParticipant $participant)
    {
        if ($event->owner_id !== auth()->id() || $participant->event_id !== $event->id) {
            abort(403);
        }

        $joinedCount = EventParticipant::where('event_id', $event->id)
            ->where('status', 'approved')
            ->count();
        if ($event->capacity && $joinedCount >= $event->capacity) {
            return back()->with('error', 'This event is already full.');
        }

        $participant->status = 'approved';
        $participant->responded_at = now();
        $participant->save();

        return back()->with('success', $participant->user->name . ' has been approved.');
    }

    public function rejectParticipant(Event $event, EventParticipant $participant)
    {
        if ($event->owner_id !== auth()->id() || $participant->event_id !== $event->id) {
            abort(403);
        }

        $participant->status = 'rejected';
        $participant->responded_at = now();
        $participant->save();

        return back()->with('success', $participant->user->name . ' has been rejected.');
    }

    public function destroyComment(Event $event, Comment $comment)
    {
        if ($comment->event_id !== $event->id) {
            abort(404);
        }

        if (auth()->id() !== $event->owner_id && auth()->id() !== $comment->user_id) {
            abort(403);
        }

        $comment->delete();

        return redirect()->route('events.show', $event)->with('success', 'The comment has been deleted.');
    }

    public function cancelEvent(Request $request, Event $event)
    {
        if ($event->owner_id !== auth()->id()) {
            abort(403);
        }

        if ($event->status === 'cancelled') {
            return back()->with('error', 'This event has already been cancelled.');
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        $event->status = 'cancelled';
        $event->cancel_reason = $request->cancel_reason;
        $event->save();

        return redirect()->route('events.show', $event)->with('success', 'The event has been cancelled.');
    }

    private function calendarData(): array
    {
        // get month and year that be displayed
        $displayMonth = request()->input('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($displayMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($displayMonth)->endOfMonth();

        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $calendarEvents = Event::where(function ($q) use ($startDate, $endDate) {
            $q->where('start_at', '<=', $endDate)->where('end_at', '>=', $startDate);
        })->orWhere(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('join_till', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
        })->get();

        // イベント日（start_at 〜 end_at）に該当する日付をすべて抽出してフラグ用配列を作る
        $eventDatesMap = [];
        foreach ($calendarEvents as $event) {
            $eventStart = Carbon::parse($event->start_at)->startOfDay();
            $eventEnd = Carbon::parse($event->end_at)->endOfDay();

            $eventPeriod = CarbonPeriod::create($eventStart, $eventEnd);
            foreach ($eventPeriod as $date) {
                $eventDatesMap[$date->format('Y-m-d')] = true;
            }
        }

        // 予約受付終了日のマップを作る
        $deadlinesMap = [];
        foreach ($calendarEvents as $event) {
            if ($event->join_till) {
                $deadlineDate = Carbon::parse($event->join_till)->format('Y-m-d');
                $deadlinesMap[$deadlineDate] = true;
            }
        }

        // カレンダーの全マス目（1日ずつ）に対して判定を行う
        $period = CarbonPeriod::create($startDate, $endDate);
        $calendarDays = [];

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $calendarDays[] = [
                'date' => $dateString,
                'is_other_month' => $date->month !== $startOfMonth->month,
                'is_event_date' => isset($eventDatesMap[$dateString]),
                'is_closed_date' => isset($deadlinesMap[$dateString]),
            ];
        }

        return [
            'calendarDays' => $calendarDays,
            'displayMonth' => $startOfMonth->format('M, Y'),
        ];
    }
}
