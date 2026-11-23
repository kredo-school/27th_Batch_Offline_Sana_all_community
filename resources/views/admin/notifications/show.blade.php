@extends('layouts.app')

@section('title', __('Notification'))

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <a href="{{ route('admin.notifications') }}" class="small text-decoration-none">
                <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Back to notifications') }}
            </a>
            <h1 class="h3 mt-3 mb-1">{{ $notification->title ?: $notification->type_label }}</h1>
            <p class="text-secondary small mb-4">
                <span class="badge bg-secondary">{{ $notification->type_label }}</span>
                &middot; {{ __('From') }}: {{ $notification->sender_name ?: '-' }}
                &middot; {{ __('To') }}: {{ $notification->recipient_name ?: '-' }}
                &middot; {{ $notification->created_at?->format('M j, Y g:i A') }}
                &middot; {{ $notification->read_at ? __('Read').' '.$notification->read_at->format('M j, Y g:i A') : __('Unread') }}
            </p>
            <p style="white-space: pre-wrap;">{{ $notification->message ?: __('No message.') }}</p>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.notifications.edit', $notification) }}" class="btn btn-outline-primary btn-sm"
                    title="{{ __('Edit') }}" aria-label="{{ __('Edit Notification') }}">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
