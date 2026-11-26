@extends('layouts.app')

@section('title', __('Edit Review'))

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <h1 class="h4 mb-1">{{ __('Edit review') }} #{{ $review->id }}</h1>
    <p class="text-secondary small mb-4">
        {{ __('By') }} {{ $review->user?->username ?: __('Unknown user') }}
        @if ($review->event)
            {{ __('on') }} <a href="{{ route('admin.events.show', $review->event) }}">{{ $review->event->title }}</a>
        @endif
    </p>

    <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="rating" class="form-label">{{ __('Rating') }}</label>
            <select name="rating" id="rating" class="form-select" required>
                @foreach ([5 => __('5 - Very good'), 4 => __('4 - Good'), 3 => __('3 - Average'), 2 => __('2 - Poor'), 1 => __('1 - Very poor')] as $value => $label)
                    <option value="{{ $value }}" @selected((int) old('rating', $review->rating) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('rating')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="comment" class="form-label">{{ __('Review') }}</label>
            <textarea name="comment" id="comment" rows="5" class="form-control" maxlength="1000">{{ old('comment', $review->comment) }}</textarea>
            @error('comment')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.reviews') }}" class="btn btn-outline-warning btn-custom-outline">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
        </div>
    </form>
</div>
@endsection

