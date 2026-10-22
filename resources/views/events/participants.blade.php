@extends('layouts.app')

@section('content')
<style>
    .participant-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 16px;
    }
    .participant-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .participant-row:last-child {
        border-bottom: none;
    }
    .participant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e6f1fb;
        color: #3d8fd6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        flex-shrink: 0;
    }
    .participant-help {
        color: #888780;
        font-size: 12px;
    }
</style>

@php
    $pending  = $participants->where('status', 'pending');
    $approved = $participants->where('status', 'approved');
    $rejected = $participants->where('status', 'rejected');
@endphp

<div class="container py-4" style="max-width: 760px;">

    <a href="{{ route('events.show', $event) }}" class="text-decoration-none text-dark d-inline-block mb-3">&larr; back to event</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <p class="text-primary fw-semibold small mb-1">PARTICIPANTS</p>
    <h2 class="fw-normal mb-1">{{ $event->title }}</h2>
    <p class="participant-help mb-4">{{ $approved->count() }}/{{ $event->capacity }} joined</p>

    @if ($isOwner)
        <div class="participant-card">
            <h5 class="mb-3">Pending requests ({{ $pending->count() }})</h5>

            @forelse ($pending as $participant)
                <div class="participant-row">
                    <div class="participant-avatar">{{ strtoupper(substr($participant->user->name ?? '?', 0, 1)) }}</div>
                    <div class="flex-grow-1">
                        <div>{{ $participant->user->name ?? 'Unknown' }}</div>
                        <div class="participant-help">Requested {{ $participant->requested_at?->diffForHumans() }}</div>
                    </div>
                    <form action="{{ route('events.participants.approve', [$event, $participant]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                    </form>
                    <form action="{{ route('events.participants.reject', [$event, $participant]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                    </form>
                </div>
            @empty
                <p class="participant-help mb-0">No pending requests.</p>
            @endforelse
        </div>
    @endif

    <div class="participant-card">
        <h5 class="mb-3">Joined ({{ $approved->count() }})</h5>

        @forelse ($approved as $participant)
            <div class="participant-row">
                <div class="participant-avatar">{{ strtoupper(substr($participant->user->name ?? '?', 0, 1)) }}</div>
                <div>
                    <div>{{ $participant->user->name ?? 'Unknown' }}</div>
                    <div class="participant-help">Joined {{ $participant->responded_at?->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <p class="participant-help mb-0">No one has joined yet.</p>
        @endforelse
    </div>

    @if ($isOwner && $rejected->isNotEmpty())
        <div class="participant-card">
            <h5 class="mb-3">Rejected ({{ $rejected->count() }})</h5>

            @foreach ($rejected as $participant)
                <div class="participant-row">
                    <div class="participant-avatar">{{ strtoupper(substr($participant->user->name ?? '?', 0, 1)) }}</div>
                    <div class="flex-grow-1">
                        <div>{{ $participant->user->name ?? 'Unknown' }}</div>
                    </div>
                    <form action="{{ route('events.participants.approve', [$event, $participant]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-primary btn-sm">Approve</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection