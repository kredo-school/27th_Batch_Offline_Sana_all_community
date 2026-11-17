<?php

namespace App\Http\Controllers;

use App\Models\EventReview;
use App\Models\UserReview;
use Illuminate\Http\Request;

class ProfileReviewController extends Controller
{
    private function ownedReview(Request $request, string $type, int $id): EventReview|UserReview
    {
        $model = match ($type) {
            'event' => EventReview::class,
            'host' => UserReview::class,
            default => abort(404),
        };
        $review = $model::with('event.owner')->findOrFail($id);
        abort_unless((int) $review->user_id === (int) $request->user()->id, 403);

        return $review;
    }

    private function returnParameters(Request $request): array
    {
        $filter = $request->input('written_type', 'all');
        return ['id' => $request->user()->id, 'tab' => 'review', 'review_type' => 'written',
            'written_type' => in_array($filter, ['all', 'event', 'host'], true) ? $filter : 'all'];
    }

    public function edit(Request $request, string $type, int $id)
    {
        $review = $this->ownedReview($request, $type, $id);
        $returnParameters = $this->returnParameters($request);

        return view('profile.review-edit', compact('review', 'type', 'returnParameters'));
    }

    public function update(Request $request, string $type, int $id)
    {
        $review = $this->ownedReview($request, $type, $id);
        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);
        $review->update($data);

        return redirect()->route('profile.show', $this->returnParameters($request))
            ->with('status', 'Review updated.');
    }

    public function destroy(Request $request, string $type, int $id)
    {
        $review = $this->ownedReview($request, $type, $id);
        $review->delete();

        return redirect()->route('profile.show', $this->returnParameters($request))
            ->with('status', 'Review deleted.');
    }
}
