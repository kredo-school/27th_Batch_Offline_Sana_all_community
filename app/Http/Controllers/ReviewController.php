<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // One review per user per event (enforced by unique index).
        EventReview::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => Auth::id()],
            $validated
        );

        return redirect()->back()->with('status', 'Review saved.');
    }

    public function edit($id)
    {
        $review = EventReview::with('event')->findOrFail($id);
        abort_unless((int) $review->user_id === (int) Auth::id(), 403);

        return view('reviews.edit', compact('review'));
    }

    public function update(Request $request, $id)
    {
        $review = EventReview::findOrFail($id);
        abort_unless((int) $review->user_id === (int) Auth::id(), 403);

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update($validated);

        return redirect()->back()->with('status', 'Review updated.');
    }

    public function destroy($id)
    {
        $review = EventReview::findOrFail($id);
        abort_unless((int) $review->user_id === (int) Auth::id(), 403);

        $review->delete();

        return redirect()->back()->with('status', 'Review deleted.');
    }
}
