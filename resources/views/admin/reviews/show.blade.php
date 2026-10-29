@extends('layouts.app')

@section('title', 'Review Details')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <a href="{{ route('admin.reviews') }}" class="small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Back to reviews
    </a>

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Review #{{ $review->id }}</h1>
            <p class="text-secondary small mb-0">
                By {{ $review->user->name ?? 'Unknown user' }}
                @if ($review->event)
                    on <a href="{{ route('admin.events.show', $review->event) }}">{{ $review->event->title }}</a>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-pen me-1" aria-hidden="true"></i>Edit
        </a>
    </div>

    <dl class="row">
        <dt class="col-sm-3">Rating</dt>
        <dd class="col-sm-9">{{ $review->rating }} / 5</dd>
        <dt class="col-sm-3">Review</dt>
        <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $review->comment ?: '-' }}</dd>
        <dt class="col-sm-3">Created</dt>
        <dd class="col-sm-9">{{ $review->created_at?->format('M j, Y g:i A') ?? '-' }}</dd>
        <dt class="col-sm-3">Updated</dt>
        <dd class="col-sm-9">{{ $review->updated_at?->format('M j, Y g:i A') ?? '-' }}</dd>
    </dl>
</div>
@endsection
