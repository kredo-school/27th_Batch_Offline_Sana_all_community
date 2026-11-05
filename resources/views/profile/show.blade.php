@extends('layouts.app')

@section('title', 'Profile · '.$user->name)

@section('content')
    @php
        $imageUrl = fn ($path, $fallback) => $path
            ? (str_starts_with($path, 'https://') || str_starts_with($path, 'http://') ? $path : asset('storage/'.$path))
            : $fallback;
        $cover = $imageUrl($user->profile?->cover_image, 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="450"></svg>'));
        $avatar = $imageUrl($user->profile?->avatar, asset('images/onlylogo.jpg'));
    @endphp
    <div class="profile-page profile-overview">
        <nav class="profile-sidebar" aria-label="Profile navigation">
            @foreach (['Profile' => 'fa-user', 'Review' => 'fa-star', 'History' => 'fa-clock-rotate-left', 'Event' => 'fa-calendar-days', 'Bookmark' => 'fa-bookmark', 'Community' => 'fa-users'] as $tab => $icon)
                <button type="button" class="profile-nav-item {{ $loop->first ? 'is-active' : '' }}"
                    data-profile-tab="{{ strtolower($tab) }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $tab }}</button>
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
                <img class="profile-cover" src="{{ $cover }}" alt="Profile cover">
                <div class="profile-details">
                    <div class="profile-avatar-wrap">
                        <img class="profile-avatar" src="{{ $avatar }}" alt="{{ $user->name }}">
                        @if (auth()->id() === $user->id)
                            <a class="profile-camera" href="{{ route('profile.edit') }}" aria-label="Change profile photo"><i class="fa-solid fa-camera" aria-hidden="true"></i></a>
                        @endif
                    </div>
                    <div class="profile-actions">
                        @if (auth()->id() === $user->id)
                            <a href="{{ route('profile.edit') }}" class="profile-edit">Edit Profile</a>
                        @elseif (auth()->check())
                            @if ($isFollowing || !$followBlocked)
                                <form action="{{ route($isFollowing ? 'profile.unfollow' : 'profile.follow', $user) }}" method="POST">
                                    @csrf
                                    @if ($isFollowing) @method('DELETE') @endif
                                    <button type="submit" class="btn {{ $isFollowing ? 'btn-outline-primary' : 'btn-primary' }}">{{ $isFollowing ? 'Unfollow' : 'Follow' }}</button>
                                </form>
                            @endif
                            @if ($isBlockedByViewer)
                                <form action="{{ route('messages.unblock', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-success">Unblock user</button>
                                </form>
                            @else
                                <form action="{{ route('messages.block', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning">Block user</button>
                                </form>
                            @endif
                        @endif
                    </div>
                    <div class="profile-identity">
                        <h1>{{ $user->name }}</h1>
                        <p class="profile-handle">{{ '@'.($user->username ?: 'user'.$user->id) }}</p>
                        <p class="profile-bio">{{ $user->profile?->bio ?: 'Welcome to my profile!' }}</p>
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
                        <form method="dialog"><button type="submit" class="btn-close" aria-label="Close list"></button></form>
                    </div>
                    <ul class="profile-connection-list">
                        @forelse ($connections as $connection)
                            <li><a href="{{ route('profile.show', $connection->id) }}">
                                <img src="{{ $imageUrl($connection->profile?->avatar, asset('images/onlylogo.jpg')) }}" alt="" width="44" height="44">
                                <span><strong>{{ $connection->name }}</strong><span>{{ '@'.($connection->username ?: 'user'.$connection->id) }}</span></span>
                            </a></li>
                        @empty
                            <li>No {{ $connectionType }} yet.</li>
                        @endforelse
                    </ul>
                    {{ $connections->links() }}
                </dialog>
            @endif

            <section class="profile-feed" aria-label="Profile activity">
                <div data-profile-panel="profile event">
                    <div class="profile-feed-heading">
                        <h2>Hosted events</h2>
                        <span class="profile-event-count">{{ $events->count() }} {{ $events->count() === 1 ? 'event' : 'events' }}</span>
                    </div>
                    @forelse ($events as $event)
                        <article class="profile-post" id="event-{{ $event->id }}">
                            <header class="profile-post-header">
                                <img class="profile-post-avatar" src="{{ $avatar }}" alt="">
                                <div class="profile-post-author">
                                    <p class="profile-post-name">{{ $user->name }}</p>
                                    <p class="profile-post-byline"><span>{{ '@'.($user->username ?: 'user'.$user->id) }}</span><span>Posted <time datetime="{{ $event->created_at->toDateString() }}">{{ $event->created_at->format('M j, Y') }}</time></span></p>
                                </div>
                                <details class="profile-post-menu"><summary aria-label="Event options"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></summary><div><button type="button" data-copy-event="{{ url()->current().'#event-'.$event->id }}"><i class="fa-solid fa-link" aria-hidden="true"></i> Copy link</button></div></details>
                            </header>
                            <div class="profile-post-body"><h3><a href="{{ route('events.show', $event) }}" class="profile-event-title">{{ $event->title }}</a></h3>
                                @if ($event->categories->isNotEmpty())
                                    <p class="profile-post-categories">@foreach ($event->categories as $category)<span class="badge">{{ $category->name }}</span>@endforeach</p>
                                @endif
                                <p>{{ $event->description }}</p>
                                @if ($event->image)<a href="{{ route('events.show', $event) }}"><img class="profile-post-image" src="{{ $imageUrl($event->image, '') }}" alt="{{ $event->title }}" loading="lazy"></a>@endif
                            </div>
                            <footer class="profile-post-footer">
                                <div class="profile-event-meta">
                                    <div class="profile-meta-item">
                                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                        <div class="profile-meta-copy">
                                            <span class="profile-meta-label">Event date</span>
                                            @if ($event->start_at)
                                                <time class="profile-meta-value" datetime="{{ $event->start_at->toDateString() }}">{{ $event->start_at->format('M j, Y') }}</time>
                                            @else
                                                <span class="profile-meta-value">Date to be announced</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="profile-meta-item">
                                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                        <div class="profile-meta-copy"><span class="profile-meta-label">Location</span><span class="profile-meta-value">{{ $event->location ?: 'Location to be announced' }}</span></div>
                                    </div>
                                </div>
                                <a href="{{ route('events.show', $event) }}" class="profile-event-details" aria-label="View details for {{ $event->title }}">View details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                            </footer>
                        </article>
                    @empty
                        <p class="profile-events-empty">No events shared yet.</p>
                    @endforelse
                </div>
                <div data-profile-panel="bookmark" hidden>
                    <div class="profile-feed-heading"><h2>Bookmarks</h2></div>
                    @if ($bookmarkedEvents !== null)
                        <div class="event-grid profile-bookmark-grid">
                            @forelse ($bookmarkedEvents as $event)
                                @php $event->is_bookmarked = true; @endphp
                                @include('events.partials.card', ['event' => $event])
                            @empty
                                <p class="profile-events-empty">No bookmarked events yet. Tap the heart on an event to save it here.</p>
                            @endforelse
                        </div>
                        {{ $bookmarkedEvents->links() }}
                    @else
                        <p class="profile-events-empty">Bookmarks are private.</p>
                    @endif
                </div>
                @foreach (['review' => 'No reviews to display.', 'history' => 'No activity history to display.', 'community' => 'No communities to display.'] as $panel => $message)
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
        if (initialTab === 'bookmark') {
            document.querySelector('[data-profile-tab="bookmark"]').click();
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
