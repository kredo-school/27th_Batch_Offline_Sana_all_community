<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Event;
use App\Models\EventCategory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

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

        // カレンダーの表示開始日（月曜始まりか日曜始まりかに合わせる。今回は日曜始まりの例）
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        // --- 【カレンダー用判定処理（数日間対応）】 ---
        // カレンダー表示範囲と重なるイベントを取得
        $calendarEvents = Event::where(function ($q) use ($startDate, $endDate) {
            $q->where('start_at', '<=', $endDate)->where('end_at', '>=', $startDate);
        })->orWhere(function ($q) use ($startDate, $endDate) {
            // 予約受付終了日がカレンダー範囲内にあるものも含める
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

        return view('events.index', [
            'events' => $events,
            'calendarDays' => $calendarDays,
            'displayMonth' => $startOfMonth->format('M, Y'),
        ]);
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

        $validated['owner_id'] = Auth::id();

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
}
