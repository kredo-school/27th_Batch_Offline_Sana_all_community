@extends('layouts.app')

@section('title', 'Notification')

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <a href="{{ route('admin.notifications') }}" class="small text-decoration-none">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to notifications
            </a>
            <h1 class="h3 mt-3 mb-1">{{ $notification->title ?: ucfirst($notification->type) }}</h1>
            <p class="text-secondary small mb-4">
                <span class="badge bg-secondary">{{ ucfirst($notification->type) }}</span>
                &middot; From: {{ $notification->sender_name ?: '-' }}
                &middot; To: {{ $notification->recipient_name ?: '-' }}
                &middot; {{ $notification->created_at?->format('M j, Y g:i A') }}
                &middot; {{ $notification->read_at ? 'Read '.$notification->read_at->format('M j, Y g:i A') : 'Unread' }}
            </p>
            <p style="white-space: pre-wrap;">{{ $notification->message ?: 'No message.' }}</p>
            <a href="{{ route('admin.notifications.edit', $notification) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-pen me-1"></i>Edit
            </a>
        </div>
    </div>
</section>
@endsection
