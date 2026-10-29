@extends('layouts.app')

@section('title', 'Edit Review')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <h1 class="h4 mb-1">Edit your review</h1>
    @if ($review->event)
        <p class="text-secondary small mb-4">for {{ $review->event->title }}</p>
    @endif

    @if (session('status'))
        <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
    @endif

    <form action="{{ route('review.update', $review->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="rating" class="form-label">Rating</label>
            <select name="rating" id="rating" class="form-select" required>
                @foreach ([5 => '5 - Very good', 4 => '4 - Good', 3 => '3 - Average', 2 => '2 - Poor', 1 => '1 - Very poor'] as $value => $label)
                    <option value="{{ $value }}" @selected((int) old('rating', $review->rating) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('rating')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="comment" class="form-label">Review</label>
            <textarea name="comment" id="comment" rows="5" class="form-control" maxlength="1000">{{ old('comment', $review->comment) }}</textarea>
            @error('comment')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-warning">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>
@endsection
