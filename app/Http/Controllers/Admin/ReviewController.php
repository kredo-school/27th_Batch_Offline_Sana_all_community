<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function edit(EventReview $review)
    {
        $review->load(['user', 'event']);

        return view('admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, EventReview $review)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update($validated);

        return redirect()->route('admin.reviews')->with('status', 'Review updated.');
    }

    public function destroy(EventReview $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews')->with('status', 'Review deleted.');
    }
}

