<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    public function index(Request $request): View
    {
        $events = $request->user()
            ->bookmarkedEvents()
            ->latest('events.created_at')
            ->paginate(12);

        return view('bookmarks.index', compact('events'));
    }

    public function store(
        Request $request,
        Event $event
    ): RedirectResponse {
        Bookmark::firstOrCreate([
            'user_id' => $request->user()->id,
            'event_id' => $event->id,
        ]);

        return back()->with(
            'success',
            // 'イベントをブックマークに追加しました。'
        );
    }

    public function destroy(
        Request $request,
        Event $event
    ): RedirectResponse {
        Bookmark::where('user_id', $request->user()->id)
            ->where('event_id', $event->id)
            ->delete();

        return back()->with(
            'success',
            // 'ブックマークを解除しました。'
        );
    }
}
