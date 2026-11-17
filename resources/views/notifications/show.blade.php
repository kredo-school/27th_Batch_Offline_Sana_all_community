@extends('layouts.app')

@section('title', $notification->title ?: __('Notification'))

@section('content')
    <div class="container py-5">
        <div class="mx-auto" style="max-width: 720px;">
            <a href="{{ url()->previous() }}" class="btn btn-link px-0 mb-3">
                &larr; {{ __('Back') }}
            </a>

            <article class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        {{ $notification->created_at?->format('M j, Y g:i A') }}
                    </p>
                    <h1 class="h4 mb-3">
                        {{ $notification->title ?: ucfirst(str_replace('_', ' ', $notification->type)) }}
                    </h1>

                    @if ($notification->sender_name)
                        <p class="small text-muted mb-3">
                            {{ __('From') }}: {{ $notification->sender_name }}
                        </p>
                    @endif

                    @if ($notification->message)
                        <p class="mb-4">{{ $notification->message }}</p>
                    @endif

                    @if (in_array($notification->type, ['event_join_approved', 'event_join_request'], true)
                        && $notification->related_id)
                        <a href="{{ route('events.show', $notification->related_id) }}" class="btn btn-primary">
                            {{ __('View event') }}
                        </a>
                    @elseif (in_array($notification->type, ['community_join_approved'], true)
                        && $notification->related_id)
                        <a href="{{ route('community.show', $notification->related_id) }}" class="btn btn-primary">
                            {{ __('View community') }}
                        </a>
                    @endif
                </div>
            </article>
        </div>
    </div>
@endsection
