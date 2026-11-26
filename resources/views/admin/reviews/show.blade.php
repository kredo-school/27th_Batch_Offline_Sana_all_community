@extends('layouts.app')

@section('title', __('Review Details'))

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <a href="{{ route('admin.reviews') }}" class="small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>{{ __('Back to reviews') }}
    </a>

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Review #{{ $review->id }}</h1>
            <p class="text-secondary small mb-0">
                By {{ $review->user?->username ?: __('Unknown user') }}
                @if ($review->event)
                    on <a href="{{ route('admin.events.show', $review->event) }}">{{ $review->event->title }}</a>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-outline-primary btn-sm"
                title="{{ __('Edit') }}" aria-label="{{ __('Edit review') }}">
                <i class="fa-solid fa-pen" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <dl class="row">
        <dt class="col-sm-3">{{ __('Rating') }}</dt>
        <dd class="col-sm-9">{{ $review->rating_label }} ({{ $review->rating }} / 5)</dd>
        <dt class="col-sm-3">Review</dt>
        <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $review->comment ?: '-' }}</dd>
        <dt class="col-sm-3">Created</dt>
        <dd class="col-sm-9">{{ \App\Support\LocalizedValue::dateTime($review->created_at) }}</dd>
        <dt class="col-sm-3">Updated</dt>
        <dd class="col-sm-9">{{ \App\Support\LocalizedValue::dateTime($review->updated_at) }}</dd>
    </dl>
</div>
@endsection
