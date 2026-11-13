@extends('layouts.app')
@section('title', 'Edit your review')
@section('content')
<div class="container py-4" style="max-width: 720px;">
    <a href="{{ route('profile.show', $returnParameters) }}#profile-activity" class="text-secondary small">&larr; Written reviews</a>
    <section class="card border-0 shadow-sm mt-3 p-4">
        <h1 class="h4">Edit your {{ $type === 'host' ? 'host' : 'event' }} review</h1>
        <p class="text-secondary">{{ $review->event?->title ?? 'Event no longer available' }}</p>
        <form action="{{ route('profile.reviews.update', ['type' => $type, 'id' => $review->id]) }}" method="POST">
            @csrf
            @method('PATCH')
            <input type="hidden" name="written_type" value="{{ $returnParameters['written_type'] }}">
            <div class="mb-3">
                <label for="rating" class="form-label">Rating</label>
                <select id="rating" name="rating" class="form-select" required>
                    @for ($rating = 5; $rating >= 1; $rating--)
                        <option value="{{ $rating }}" @selected((int) old('rating', $review->rating) === $rating)>{{ $rating }} / 5</option>
                    @endfor
                </select>
                @error('rating')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label for="comment" class="form-label">Comment (optional)</label>
                <textarea id="comment" name="comment" class="form-control" rows="5" maxlength="1000">{{ old('comment', $review->comment) }}</textarea>
                @error('comment')<p class="text-danger small mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('profile.show', $returnParameters) }}#profile-activity" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection
