@extends('layouts.app')

@section('title', 'Comment Details')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <a href="{{ route('admin.comments') }}" class="small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Back to comments
    </a>

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Comment #{{ $comment->id }}</h1>
            <p class="text-secondary small mb-0">
                By {{ $comment->user->name ?? 'Unknown user' }}
                @if ($comment->event)
                    on <a href="{{ route('admin.events.show', $comment->event) }}">{{ $comment->event->title }}</a>
                @endif
            </p>
        </div>
        <a href="{{ route('admin.comments.edit', $comment) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-pen me-1" aria-hidden="true"></i>Edit
        </a>
    </div>

    <dl class="row">
        <dt class="col-sm-3">Comment</dt>
        <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $comment->content }}</dd>
        <dt class="col-sm-3">Language</dt>
        <dd class="col-sm-9">{{ strtoupper($comment->language ?: '-') }}</dd>
        <dt class="col-sm-3">Created</dt>
        <dd class="col-sm-9">{{ $comment->created_at?->format('M j, Y g:i A') ?? '-' }}</dd>
        <dt class="col-sm-3">Updated</dt>
        <dd class="col-sm-9">{{ $comment->updated_at?->format('M j, Y g:i A') ?? '-' }}</dd>
    </dl>
</div>
@endsection
