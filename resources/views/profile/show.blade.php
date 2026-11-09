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
    <div class="profile-page profile-overview {{ (int) auth()->id() !== (int) $user->id ? 'profile-visitor' : '' }}">
        @if ((int) auth()->id() === (int) $user->id)
            @include('profile.partials.navigation')
        @endif

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
                        @if ((int) auth()->id() === (int) $user->id)
                            <a href="{{ route('profile.edit') }}" class="profile-edit">Edit Profile</a>
                        @elseif (auth()->check())
                            <form action="{{ route($isFollowing ? 'profile.unfollow' : 'profile.follow', $user) }}" method="POST">
                                @csrf
                                @if ($isFollowing) @method('DELETE') @endif
                                <button type="submit" class="profile-follow-button {{ $isFollowing ? 'is-following' : '' }}"
                                    @disabled(!$isFollowing && $followBlocked)
                                    @if (!$isFollowing && $followBlocked) title="Following is unavailable while either user has blocked the other." @endif><i class="fa-solid {{ $isFollowing ? 'fa-user-check' : 'fa-user-plus' }}" aria-hidden="true"></i><span>{{ $isFollowing ? 'Unfollow' : 'Follow' }}</span></button>
                            </form>
                            <div class="dropdown profile-more-actions">
                                <button type="button" class="profile-more-button" data-bs-toggle="dropdown"
                                    aria-expanded="false" aria-label="More user actions">
                                    <i class="fa-solid fa-ellipsis" aria-hidden="true"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                @if ($isBlockedByViewer)
                                <form action="{{ route('messages.unblock', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item"><i class="fa-solid fa-unlock" aria-hidden="true"></i>Unblock user</button>
                                </form>
                            @else
                                <form action="{{ route('messages.block', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-ban" aria-hidden="true"></i>Block user</button>
                                </form>
                                @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="profile-identity">
                        <h1>{{ $user->name }}</h1>
                        <p class="profile-handle">{{ '@'.($user->username ?: 'user'.$user->id) }}</p>
                        <p class="profile-bio">{{ $user->profile?->bio ?: 'Welcome to my profile!' }}</p>
                    </div>
                    <dl class="profile-stats">
                        @foreach (['Events Joined' => $user->attending_events_count, 'Events Hosted' => $user->events_owned_count, 'Followers' => $user->followers_count, 'Following' => $user->following_count] as $label => $count)
                            <div><dt>@if (in_array($label, ['Followers', 'Following']))<a href="{{ route('profile.show', ['id' => $user->id, 'connections' => strtolower($label), 'tab' => $activeTab]) }}">{{ $label }}</a>@else{{ $label }}@endif</dt><dd>@if (in_array($label, ['Followers', 'Following']))<a href="{{ route('profile.show', ['id' => $user->id, 'connections' => strtolower($label), 'tab' => $activeTab]) }}" aria-label="{{ $label }}: {{ $count }}">{{ $count }}</a>@else{{ $count }}@endif</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>

            @if ($connections !== null)
                <dialog class="profile-connections" id="profile-connections-dialog" aria-labelledby="profile-connections-title">
                    <div class="profile-feed-heading">
                        <h2 id="profile-connections-title">{{ $connectionType === 'people' ? 'Find people' : ucfirst($connectionType) }} ({{ $connections->total() }})</h2>
                        <form method="dialog"><button type="submit" class="btn-close" aria-label="Close list"></button></form>
                    </div>
                    <form method="GET" action="{{ route('profile.show', $user->id) }}" class="profile-connection-search">
                        <input type="hidden" name="connections" value="{{ $connectionType }}">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <label class="visually-hidden" for="connection-search">Search by name or username</label>
                        <input type="search" id="connection-search" name="connection_search" value="{{ $connectionSearch }}" maxlength="100" placeholder="Search name or username">
                        <button type="submit">Search</button>
                    </form>
                    <ul class="profile-connection-list">
                        @forelse ($connections as $connection)
                            <li><a href="{{ route('profile.show', $connection->id) }}">
                                <img src="{{ $imageUrl($connection->profile?->avatar, asset('images/onlylogo.jpg')) }}" alt="" width="44" height="44">
                                <span class="profile-connection-info"><strong>{{ $connection->name }}</strong><span>{{ '@'.($connection->username ?: 'user'.$connection->id) }}</span>
                                    @if ((int) $connection->id !== (int) auth()->id() && $connection->follows_viewer)
                                        <span class="profile-relationship">{{ $connection->followed_by_viewer ? 'Mutual' : 'Follows you' }}</span>
                                    @endif
                                </span>
                            </a>
                            @if ((int) $connection->id !== (int) auth()->id())
                                <form method="POST" action="{{ route($connection->followed_by_viewer ? 'profile.unfollow' : 'profile.follow', $connection) }}">
                                    @csrf
                                    @if ($connection->followed_by_viewer) @method('DELETE') @endif
                                    <input type="hidden" name="connections_owner" value="{{ $user->id }}">
                                    <input type="hidden" name="connections" value="{{ $connectionType }}">
                                    <input type="hidden" name="connection_search" value="{{ $connectionSearch }}">
                                    <input type="hidden" name="connections_page" value="{{ $connections->currentPage() }}">
                                    <input type="hidden" name="tab" value="{{ $activeTab }}">
                                    <button type="submit" class="profile-follow-button {{ $connection->followed_by_viewer ? 'is-following' : '' }}" @disabled(!$connection->followed_by_viewer && in_array((int) $connection->id, $connectionBlockedIds, true)) aria-label="{{ ($connection->followed_by_viewer ? 'Unfollow ' : 'Follow ').$connection->name }}">{{ $connection->followed_by_viewer ? 'Unfollow' : 'Follow' }}</button>
                                </form>
                            @endif
                            </li>
                        @empty
                            <li>{{ $connectionSearch !== '' ? 'No users found.' : 'No '.$connectionType.' yet.' }}</li>
                        @endforelse
                    </ul>
                    {{ $connections->links() }}
                </dialog>
            @endif

            @if (!in_array($activeTab, ['feed', 'bookmark', 'history']))
                <nav class="profile-content-tabs" aria-label="User profile sections">
                    @foreach (['event' => 'Events', 'review' => 'Reviews', 'community' => 'Communities'] as $tab => $label)
                        <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => $tab]) }}" @if($activeTab === $tab) aria-current="page" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
            @endif
            <section class="profile-feed" aria-label="Profile activity">
                <div data-profile-panel="feed" @if($activeTab !== 'feed') hidden @endif>
                    <div class="profile-feed-heading"><h2>Posts from people you follow</h2></div>
                    <div class="event-grid profile-bookmark-grid">
                        @forelse ($followingEvents as $event)
                            <div>
                                <a class="profile-feed-author" href="{{ route('profile.show', $event->owner_id) }}">
                                    {{ $event->owner->name }}
                                </a>
                                @include('events.partials.card', ['event' => $event])
                            </div>
                        @empty
                            <div class="profile-feed-empty">
                                <i class="fa-solid fa-user-group" aria-hidden="true"></i>
                                <h3>No posts yet</h3>
                                <p>Follow people to see their public posts here.</p>
                                <a class="profile-follow-button" href="{{ route('profile.show', ['id' => auth()->id(), 'tab' => 'feed', 'connections' => 'people']) }}">Find people</a>
                            </div>
                        @endforelse
                    </div>
                    {{ $followingEvents->links() }}
                </div>
                <div data-profile-panel="event" @if($activeTab !== 'event') hidden @endif>
                    <div class="profile-feed-heading">
                        <h2>Hosted events</h2>
                        <span class="profile-event-count">{{ $events->count() }} {{ $events->count() === 1 ? 'event' : 'events' }}</span>
                    </div>
                    <div class="event-grid profile-bookmark-grid">
                        @forelse ($events as $event)
                            <div id="event-{{ $event->id }}">
                                @include('events.partials.card', ['event' => $event])
                            </div>
                        @empty
                            <p class="profile-events-empty">No events shared yet.</p>
                        @endforelse
                    </div>
                </div>
                @if ((int) auth()->id() === (int) $user->id)
                <div data-profile-panel="bookmark" @if($activeTab !== 'bookmark') hidden @endif>
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
                <div data-profile-panel="history" @if($activeTab !== 'history') hidden @endif>
                    <div class="profile-feed-heading"><h2>Joined events</h2></div>
                    @if ($historyEvents !== null)
                        <div class="event-grid profile-bookmark-grid">
                            @forelse ($historyEvents as $event)
                                <div>
                                    <p class="mb-2"><span class="badge {{ $event->pivot->status === 'pending' ? 'bg-warning text-dark' : 'bg-success' }}">{{ $event->pivot->status === 'pending' ? 'Pending approval' : 'Joined' }}</span></p>
                                    @include('events.partials.card', ['event' => $event])
                                </div>
                            @empty
                                <p class="profile-events-empty">No joined events yet.</p>
                            @endforelse
                        </div>
                        {{ $historyEvents->links() }}
                    @else
                        <p class="profile-events-empty">Event history is private.</p>
                    @endif
                </div>
                @endif
                <div data-profile-panel="community" @if($activeTab !== 'community') hidden @endif>
                    <div class="profile-feed-heading"><h2>Joined communities</h2><span>{{ $joinedCommunities->total() }}</span></div>
                    <div class="event-grid profile-bookmark-grid">
                        @forelse ($joinedCommunities as $community)
                            <article class="card h-100 overflow-hidden">
                                <a href="{{ route('community.show', $community->id) }}">
                                    <img src="{{ $imageUrl($community->image, asset('images/onlylogo.jpg')) }}" alt="{{ $community->name }}" class="w-100" style="height: 160px; object-fit: cover;" loading="lazy">
                                </a>
                                <div class="card-body">
                                    <h3 class="h5"><a href="{{ route('community.show', $community->id) }}">{{ $community->name }}</a></h3>
                                    <p>{{ \Illuminate\Support\Str::limit($community->description, 150) }}</p>
                                    <a href="{{ route('community.show', $community->id) }}">View community &gt;</a>
                                </div>
                            </article>
                        @empty
                            <p class="profile-events-empty">No joined communities yet.</p>
                        @endforelse
                    </div>
                    {{ $joinedCommunities->links() }}
                </div>
                @foreach (['review' => 'No reviews to display.'] as $panel => $message)
                    <div class="profile-tab-placeholder" data-profile-panel="{{ $panel }}" @if($activeTab !== $panel) hidden @endif><h2>{{ ucfirst($panel) }}</h2><p>{{ $message }}</p></div>
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
                url.searchParams.delete('connection_search');
                window.history.replaceState(null, '', url);
            });
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
