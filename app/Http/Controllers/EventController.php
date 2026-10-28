<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventParticipant;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\EventReview;
use App\Models\UserReview;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        // get categories
        $categories = Category::query()
            ->orderBy('name')
            ->pluck('name', 'id');

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
            $request->filled('category') &&
                $request->input('category') !== 'all',
            function ($query) use ($request) {
                $query->whereHas('categories', function ($categoryQuery) use ($request) {
                    $categoryQuery->where('categories.id', $request->input('category'));
                });
            }
        );

        // sort
        $allowedSorts = [
            'date_asc',
            'date_desc',
            'created_desc',
        ];

        if ($request->filled('sort')) {
            // URLにsortがある場合は、その値を使用してセッションに保存
            $sort = in_array($request->input('sort'), $allowedSorts, true)
                ? $request->input('sort')
                : 'date_asc';

            $request->session()->put('events.sort', $sort);
        } else {
            // URLにsortがない場合は、最後に保存した値を使用
            $sort = $request->session()->get('events.sort', 'date_asc');
        }

        match ($sort) {
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

        ////////////// Event ////////////////////////
        $events = $query
            ->with(['categories'])
            ->paginate(24)
            ->withQueryString();

        // 現在表示しているイベントの中で、ログインユーザーがブックマーク済みのIDを取得
        $bookmarkedEventIds = collect();

        if (Auth::check()) {
            $bookmarkedEventIds = Bookmark::query()
                ->where('user_id', Auth::id())
                ->whereIn(
                    'event_id',
                    $events->getCollection()->pluck('id')
                )
                ->pluck('event_id')
                ->flip();
        }

        // 表示用の値を付与
        $events->getCollection()->transform(function ($event) use ($bookmarkedEventIds) {
            $event->category_name = ucfirst($event->category);

            $event->status_label = $event->start_at->isToday()
                ? 'Today'
                : $event->start_at->diffForHumans();

            $event->status_color = 'blue';

            // ここで実際の登録状態を設定
            $event->is_bookmarked = $bookmarkedEventIds->has($event->id);

            $event->is_owner = Auth::check() && (int) $event->owner_id === (int) Auth::id();

            return $event;
        });

        return view('events.index', array_merge(
            [
                'events' => $events,
                'categories' => $categories,
                'sort' => $sort
            ],
            $this->calendarData()
        ));
    }

    public function show(Event $event)
    {
        $event->load(['owner', 'categories', 'comments.user', 'reviews.user', 'userReviews']);
        $myParticipation = null;
        if (auth()->check()) {
            $myParticipation = EventParticipant::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->first();
        }
        $hostEventIds = Event::where('owner_id', $event->owner_id)->pluck('id');
        $hostRating = round((float) UserReview::whereIn('event_id', $hostEventIds)->avg('rating'), 1);
        $hostReviewCount = UserReview::whereIn('event_id', $hostEventIds)->count();
        return view('events.detail', array_merge(
            compact('event', 'myParticipation', 'hostRating', 'hostReviewCount'),
            $this->calendarData()
        ));
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
            'category_id'       => 'required|integer|exists:categories,id',
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
        if ($event->owner_id !== Auth::id()) {
            abort(403);
        }

        $categories = Category::all();
        $selectedCategoryId = EventCategory::where('event_id', $event->id)->value('category_id');

        return view('events.edit', array_merge(compact('event', 'categories', 'selectedCategoryId'), $this->calendarData()));
    }

    public function update(Request $request, Event $event)
    {
        // 主催者以外は保存できない
        if ($event->owner_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title'             => 'required|string|max:60',
            'category_id'       => 'required|integer|exists:categories,id',
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
        if ($event->end_at->isPast()) {
        return back()->with('error', 'Comments are closed because this event has ended.');
        } 
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
            ->where('user_id', Auth::id())
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
        $participant->user_id = Auth::id();
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
            ->where('user_id', Auth::id())
            ->delete();

        return redirect()->route('events.show', $event)->with('success', 'Your request has been cancelled.');
    }

    public function participants(Event $event)
    {
        $isOwner = $event->owner_id === Auth::id();
        $participants = EventParticipant::with('user')
            ->where('event_id', $event->id)
            ->when(! $isOwner, fn($query) => $query->where('status', 'approved'))
            ->orderBy('requested_at')
            ->get();

        return view('events.participants', array_merge(compact('event', 'participants', 'isOwner'), $this->calendarData()));
    }

    public function approveParticipant(Event $event, EventParticipant $participant)
    {
        if ($event->owner_id !== Auth::id() || $participant->event_id !== $event->id) {
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
        if ($event->owner_id !== Auth::id() || $participant->event_id !== $event->id) {
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

        if (Auth::id() !== $event->owner_id && Auth::id() !== $comment->user_id) {
            abort(403);
        }

        $comment->delete();

        return redirect()->route('events.show', $event)->with('success', 'The comment has been deleted.');
    }

    public function cancelEvent(Request $request, Event $event)
    {
        if ($event->owner_id !== Auth::id()) {
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

    public function storeReview(Request $request, Event $event)
    {
        // イベントが終わっていなければ止める
        if (! $event->end_at->isPast()) {
            return back()->with('error', 'You can write a review after the event ends.');
        }

        // 中止されたイベントは止める
        if ($event->status === 'cancelled') {
            return back()->with('error', 'This event has been cancelled.');
        }

        // 参加確定した人だけ
        $joined = EventParticipant::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();
        if (! $joined) {
            abort(403);
        }

        // すでにレビューしていたら止める
        $alreadyReviewed = EventReview::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->exists();
        if ($alreadyReviewed) {
            return back()->with('error', 'You have already reviewed this event.');
        }

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $review = new EventReview();
        $review->event_id = $event->id;
        $review->user_id = auth()->id();
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->save();

        return redirect()->route('events.show', $event)->with('success', 'Thank you for your review!');
    }

    public function storeHostReview(Request $request, Event $event)
    {
        if (! $event->end_at->isPast()) {
            return back()->with('error', 'You can review the host after the event ends.');
        }

        if ($event->status === 'cancelled') {
            return back()->with('error', 'This event has been cancelled.');
        }

        // 主催者は自分をレビューできない
        if ($event->owner_id === auth()->id()) {
            abort(403);
        }

        // 参加確定した人だけ
        $joined = EventParticipant::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->exists();
        if (! $joined) {
            abort(403);
        }

        $alreadyReviewed = UserReview::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->exists();
        if ($alreadyReviewed) {
            return back()->with('error', 'You have already reviewed the host.');
        }

        $request->validate([
            'host_rating'  => 'required|integer|min:1|max:5',
            'host_comment' => 'nullable|string|max:500',
        ]);

        $review = new UserReview();
        $review->event_id = $event->id;
        $review->user_id = auth()->id();
        $review->rating = $request->host_rating;
        $review->comment = $request->host_comment;
        $review->save();

        return redirect()->route('events.show', $event)->with('success', 'Thank you for reviewing the host!');
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

    public function destroy(Event $event)
    {
        if ((int) $event->owner_id !== (int) Auth::id()) {
            abort(403);
        }

        $imagePath = $event->image;
        $event->categories()->detach();

        DB::transaction(function () use ($event) {
            $event->categories()->detach();
            Bookmark::where('event_id', $event->id)->delete();
            EventParticipant::where('event_id', $event->id)->delete();
            Comment::where('event_id', $event->id)->delete();
        });

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event has been deleted.');
    }

    /////////// Bookmark /////////////////////
    public function addBookmark(Event $event)
    {
        Bookmark::firstOrCreate([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
        ]);

        return back()->with(
            'success',
            'Event added to bookmarks.'
        );
    }

    public function removeBookmark(Event $event)
    {
        Bookmark::query()
            ->where('user_id', Auth::id())
            ->where('event_id', $event->id)
            ->delete();

        return back()->with(
            'success',
            'Event removed from bookmarks.'
        );
    }

    public function bookmarkIndex()
    {
        $events = Event::query()
            ->whereIn(
                'id',
                Bookmark::query()
                    ->where('user_id', Auth::id())
                    ->select('event_id')
            )
            ->with(['categories'])
            ->orderBy('start_at')
            ->paginate(10);

        $events->getCollection()->transform(function ($event) {
            $event->category_name = ucfirst($event->category);

            $event->status_label = $event->start_at->isToday()
                ? 'Today'
                : $event->start_at->diffForHumans();

            $event->status_color = 'blue';
            $event->is_bookmarked = true;

            return $event;
        });

        return view('bookmarks.index', compact('events'));
    }
}
