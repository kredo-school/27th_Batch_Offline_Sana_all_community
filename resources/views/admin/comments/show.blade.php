@extends('layouts.app')

@section('title', __('Comment Details'))

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <a href="{{ route('admin.comments') }}" class="small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>{{ __('Back to comments') }}
    </a>

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
        <div>
            <h1 class="h4 mb-1">{{ __('Comment')}} #{{ $comment->id }}</h1>
            <p class="text-secondary small mb-0">
                {{ __('By')}} {{ $comment->user?->username ?: __('Unknown user') }}
                @if ($comment->event)
                    {{ __('on')}} <a href="{{ route('admin.events.show', $comment->event) }}">{{ $comment->event->title }}</a>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.comments.edit', $comment) }}" class="btn btn-outline-primary btn-sm"
                title="{{ __('Edit') }}" aria-label="{{ __('Edit comment') }}">
                <i class="fa-solid fa-pen" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <dl class="row">
        <dt class="col-sm-3">{{ __('Comment')}}</dt>
        <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $comment->content }}</dd>
        <dt class="col-sm-3">{{ __('Language')}}</dt>
        <dd class="col-sm-9">{{ $comment->language_label }}</dd>
        <dt class="col-sm-3">{{ __('Created')}}</dt>
        <dd class="col-sm-9">{{ \App\Support\LocalizedValue::dateTime($comment->created_at) }}</dd>
        <dt class="col-sm-3">{{ __('Updated')}}</dt>
        <dd class="col-sm-9">{{ \App\Support\LocalizedValue::dateTime($comment->updated_at) }}</dd>
    </dl>
</div>
@endsection
