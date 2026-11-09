@extends('layouts.app')
@section('title', 'Block list')
@section('content')
<div class="profile-page profile-editor">
    @include('profile.partials.editor-sidebar')
    <div class="profile-blocked-content">
        <header class="profile-editor-heading">
            <h1>Block list</h1>
            <p class="profile-editor-intro">Manage the people you have blocked. Only you can see this list.</p>
            <a href="{{ route('profile.edit') }}" class="profile-blocked-back" aria-label="Back to Edit Profile" title="Back to Edit Profile"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i></a>
        </header>
        @if (session('status'))
            <div class="alert alert-success mt-3" role="status">{{ session('status') }}</div>
        @endif
        <section class="profile-editor-section mt-4" aria-label="Blocked users">
            <ul class="profile-blocked-list">
                @forelse ($blockedUsers as $block)
                    @php
                        $blockedUser = $block->user;
                        $avatarPath = $blockedUser->profile?->avatar;
                        $avatarUrl = $avatarPath
                            ? (str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://') ? $avatarPath : asset('storage/'.$avatarPath))
                            : asset('images/onlylogo.jpg');
                    @endphp
                    <li>
                        <a class="profile-blocked-user" href="{{ route('profile.show', $blockedUser->id) }}">
                            <img src="{{ $avatarUrl }}" alt="" width="48" height="48">
                            <span><strong>{{ $blockedUser->name }}</strong><small>{{ '@'.($blockedUser->username ?: 'user'.$blockedUser->id) }}</small></span>
                        </a>
                        <form action="{{ route('messages.unblock', $blockedUser) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="profile-unblock-button">Unblock</button>
                        </form>
                    </li>
                @empty
                    <li class="profile-blocked-empty">You haven't blocked anyone.</li>
                @endforelse
            </ul>
            {{ $blockedUsers->links() }}
        </section>
    </div>
</div>
@endsection
