@extends('layouts.app')

@section('title', 'Notification')

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <a href="{{ route('notifications.index') }}" class="small text-decoration-none">
                <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Back to notifications') }}
            </a>
            <h1 class="h3 mt-3 mb-1">{{ $notification->title ?: ucfirst($notification->type) }}</h1>
            <p class="text-secondary small mb-4">
                <span class="badge bg-secondary">{{ ucfirst($notification->type) }}</span>
                @if ($notification->sender_name)
                    &middot; {{ __('From') }}: {{ $notification->sender_name }}
                @endif
                &middot; {{ $notification->created_at?->format('M j, Y g:i A') }}
            </p>
            <p class="mb-0" style="white-space: pre-wrap;">{{ $notification->message ?: __('No message.') }}</p>
        </div>
    </div>
</section>
@endsection
