@extends('layouts.app')

@section('title', __('Profile').' · '.$user->name)

@section('content')
    @php
        $imageUrl = fn ($path, $fallback) => $path
            ? (str_starts_with($path, 'https://') || str_starts_with($path, 'http://') ? $path : asset('storage/'.$path))
            : $fallback;
        $cover = $imageUrl($user->profile?->cover_image, 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="450"></svg>'));
        $avatar = $imageUrl($user->profile?->avatar, asset('images/onlylogo.jpg'));
    @endphp
    <div class="profile-page profile-overview">
        <nav class="profile-sidebar" aria-label="{{ __('Profile navigation') }}">
            @foreach (['profile' => [__('Profile'), 'fa-user'], 'review' => [__('Review'), 'fa-star'], 'history' => [__('History'), 'fa-clock-rotate-left'], 'event' => [__('Event'), 'fa-calendar-days'], 'bookmark' => [__('Bookmark'), 'fa-bookmark'], 'community' => [__('Community'), 'fa-users']] as $tab => [$label, $icon])
                <button type="button" class="profile-nav-item {{ $loop->first ? 'is-active' : '' }}"
                    data-profile-tab="{{ $tab }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}</button>
            @endforeach
        </nav>

        <div class="profile-main">
            @if (session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('status'))
                <div class="alert alert-success" role="status">{{ session('status') }}</div>
            @endif
            <section class="profile-card" aria-label="{{ $user->name }}'s profile">
                <img class="profile-cover" src="{{ $cover }}" alt="{{ __('Profile cover') }}">
                <div class="profile-details">
                    <div class="profile-avatar-wrap">
                        <img class="profile-avatar" src="{{ $avatar }}" alt="{{ $user->name }}">
                        @if (auth()->id() === $user->id)
                            <a class="profile-camera" href="{{ route('profile.edit') }}" aria-label="{{ __('Change profile photo') }}"><i class="fa-solid fa-camera" aria-hidden="true"></i></a>
                        @endif
                    </div>
                    <div class="profile-actions">
                        @if (auth()->id() === $user->id)
                            <a href="{{ route('profile.edit') }}" class="profile-edit">{{ __('Edit Profile') }}</a>
                        @elseif (auth()->check())
                            @if ($isFollowing || !$followBlocked)
                                <form action="{{ route($isFollowing ? 'profile.unfollow' : 'profile.follow', $user) }}" method="POST">
                                    @csrf
                                    @if ($isFollowing) @method('DELETE') @endif
                                    <button type="submit" class="btn {{ $isFollowing ? 'btn-outline-primary' : 'btn-primary' }}">{{ $isFollowing ? __('Unfollow') : __('Follow') }}</button>
                                </form>
                            @endif
                            @if ($isBlockedByViewer)
                                <form action="{{ route('messages.unblock', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-success">{{ __('Unblock user') }}</button>
                                </form>
                            @else
                                <form action="{{ route('messages.block', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning">{{ __('Block user') }}</button>
                                </form>
                            @endif
                        @endif
                    </div>
                    <div class="profile-identity">
                        <h1>{{ $user->name }}</h1>
                        <p class="profile-handle">{{ '@'.($user->username ?: 'user'.$user->id) }}</p>
                        <p class="profile-bio">{{ $user->profile?->bio ?: __('Welcome to my profile!') }}</p>
                    </div>
                    <dl class="profile-stats">
                        @foreach (['Events Joined' => $user->attending_events_count, 'Events Hosted' => $user->events_owned_count, 'Followers' => $user->followers_count, 'Following' => $user->following_count] as $label => $count)
                            <div><dt>@if (in_array($label, ['Followers', 'Following']))<a href="{{ route('profile.show', ['id' => $user->id, 'connections' => strtolower($label)]) }}">{{ $label }}</a>@else{{ $label }}@endif</dt><dd>{{ $count }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>

            @if ($connections !== null)
                <dialog class="profile-connections" id="profile-connections-dialog" aria-labelledby="profile-connections-title">
                    <div class="profile-feed-heading">
                        <h2 id="profile-connections-title">{{ ucfirst($connectionType) }} ({{ $connections->total() }})</h2>
                        <form method="dialog"><button type="submit" class="btn-close" aria-label="{{ __('Close list') }}"></button></form>
                    </div>
                    <ul class="profile-connection-list">
                        @forelse ($connections as $connection)
                            <li><a href="{{ route('profile.show', $connection->id) }}">
                                <img src="{{ $imageUrl($connection->profile?->avatar, asset('images/onlylogo.jpg')) }}" alt="" width="44" height="44">
                                <span><strong>{{ $connection->name }}</strong><span>{{ '@'.($connection->username ?: 'user'.$connection->id) }}</span></span>
                            </a></li>
                        @empty
                            <li>{{ __('No connections yet.') }}</li>
                        @endforelse
                    </ul>
                    {{ $connections->links() }}
                </dialog>
            @endif

            <section class="profile-feed" aria-label="Profile activity">
                <div data-profile-panel="profile event">
                    <div class="profile-feed-heading">
                        <h2>{{ __('Hosted events') }}</h2>
                        <span class="profile-event-count">{{ $events->count() }} {{ $events->count() === 1 ? __('event') : __('events') }}</span>
                    </div>
                    <div class="event-grid profile-bookmark-grid">
                        @forelse ($events as $event)
                            <div id="event-{{ $event->id }}">
                                @include('events.partials.card', ['event' => $event])
                            </div>
                        @empty
                            <p class="profile-events-empty">{{ __('No events shared yet.') }}</p>
                        @endforelse
                    </div>
                </div>
                <div data-profile-panel="bookmark" hidden>
                    <div class="profile-feed-heading"><h2>{{ __('Bookmarks') }}</h2></div>
                    @if ($bookmarkedEvents !== null)
                        <div class="event-grid profile-bookmark-grid">
                            @forelse ($bookmarkedEvents as $event)
                                @php $event->is_bookmarked = true; @endphp
                                @include('events.partials.card', ['event' => $event])
                            @empty
                                <p class="profile-events-empty">{{ __('No bookmarked events yet. Tap the heart on an event to save it here.') }}</p>
                            @endforelse
                        </div>
                        {{ $bookmarkedEvents->links() }}
                    @else
                        <p class="profile-events-empty">{{ __('Bookmarks are private.') }}</p>
                    @endif
                </div>
                <div data-profile-panel="history" hidden>
                    <div class="profile-feed-heading"><h2>{{ __('Joined events') }}</h2></div>
                    @if ($historyEvents !== null)
                        <div class="event-grid profile-bookmark-grid">
                            @forelse ($historyEvents as $event)
                                <div>
                                    <p class="mb-2"><span class="badge {{ $event->pivot->status === 'pending' ? 'bg-warning text-dark' : 'bg-success' }}">{{ $event->pivot->status === 'pending' ? 'Pending approval' : 'Joined' }}</span></p>
                                    @include('events.partials.card', ['event' => $event])
                                </div>
                            @empty
                                <p class="profile-events-empty">{{ __('No joined events yet.') }}</p>
                            @endforelse
                        </div>
                        {{ $historyEvents->links() }}
                    @else
                    <p class="profile-events-empty">{{ __('Event history is private.') }}</p>
                    @endif
                </div>
                <div data-profile-panel="community" hidden>
                    <div class="profile-feed-heading"><h2>{{ __('Joined communities') }}</h2><span>{{ $joinedCommunities->total() }}</span></div>
                    <div class="event-grid profile-bookmark-grid">
                        @forelse ($joinedCommunities as $community)
                            <article class="card h-100 overflow-hidden">
                                <a href="{{ route('community.show', $community->id) }}">
                                    <img src="{{ $imageUrl($community->image, asset('images/onlylogo.jpg')) }}" alt="{{ $community->name }}" class="w-100" style="height: 160px; object-fit: cover;" loading="lazy">
                                </a>
                                <div class="card-body">
                                    <h3 class="h5"><a href="{{ route('community.show', $community->id) }}">{{ $community->name }}</a></h3>
                                    <p>{{ \Illuminate\Support\Str::limit($community->description, 150) }}</p>
                                    <a href="{{ route('community.show', $community->id) }}">{{ __('View community') }} &gt;</a>
                                </div>
                            </article>
                        @empty
                            <p class="profile-events-empty">{{ __('No joined communities yet.') }}</p>
                        @endforelse
                    </div>
                    {{ $joinedCommunities->links() }}
                </div>
                @foreach (['review' => 'No reviews to display.'] as $panel => $message)
                    <div class="profile-tab-placeholder" data-profile-panel="{{ $panel }}" hidden><h2>{{ ucfirst($panel) }}</h2><p>{{ $message }}</p></div>
                @endforeach
                <p class="profile-feedback" role="status" aria-live="polite"></p>
            </section>
        </div>
    </div>
    <script>
        const connectionsDialog = document.getElementById('profile-connections-dialog');
        if (connectionsDialog) {
            connectionsDialog.showModal();
            connectionsDialog.addEventListener('click', event => {
                const bounds = connectionsDialog.getBoundingClientRect();
                if (event.target === connectionsDialog &&
                    (event.clientX < bounds.left || event.clientX > bounds.right ||
                     event.clientY < bounds.top || event.clientY > bounds.bottom)) {
                    connectionsDialog.close();
                }
            });
            connectionsDialog.addEventListener('close', () => {
                const url = new URL(window.location.href);
                url.searchParams.delete('connections');
                url.searchParams.delete('connections_page');
                window.history.replaceState(null, '', url);
            });
        }
        document.querySelectorAll('[data-profile-tab]').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('[data-profile-tab]').forEach(item => {
                    const active = item === button;
                    item.classList.toggle('is-active', active);
                    item.setAttribute('aria-pressed', String(active));
                });
                document.querySelectorAll('[data-profile-panel]').forEach(panel => {
                    panel.hidden = !panel.dataset.profilePanel.split(' ').includes(button.dataset.profileTab);
                });
            });
        });
        const initialTab = new URLSearchParams(window.location.search).get('tab');
        const initialTabButton = Array.from(document.querySelectorAll('[data-profile-tab]'))
            .find(button => button.dataset.profileTab === initialTab);
        if (initialTabButton) {
            initialTabButton.click();
        }
        document.querySelectorAll('[data-copy-event]').forEach(button => {
            button.addEventListener('click', async () => {
                const feedback = document.querySelector('.profile-feedback');
                try {
                    await navigator.clipboard.writeText(button.dataset.copyEvent);
                    feedback.textContent = 'Link copied.';
                } catch {
                    feedback.textContent = 'Copy this link: ' + button.dataset.copyEvent;
                }
                button.closest('details').open = false;
            });
        });
    </script>
@endsection
