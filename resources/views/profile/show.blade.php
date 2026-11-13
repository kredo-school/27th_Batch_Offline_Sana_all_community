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
                <img class="profile-cover" src="{{ $cover }}" alt="{{ __('Profile cover') }}">
                <div class="profile-details">
                    <div class="profile-avatar-wrap">
                        <img class="profile-avatar" src="{{ $avatar }}" alt="{{ $user->name }}">
                        @if (auth()->id() === $user->id)
                            <a class="profile-camera" href="{{ route('profile.edit') }}" aria-label="{{ __('Change profile photo') }}"><i class="fa-solid fa-camera" aria-hidden="true"></i></a>
                        @endif
                    </div>
                    <div class="profile-identity">
                        <div class="profile-identity-heading">
                            <div class="profile-identity-name">
                                <h1>{{ $user->name }}</h1>
                                <p class="profile-handle">{{ '@'.($user->username ?: 'user'.$user->id) }}</p>
                            </div>
                        <div class="profile-identity-tools">
                        <a class="profile-host-rating" href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'review', 'review_type' => 'host']) }}#profile-activity">
                            <span class="visually-hidden">Host rating: </span>
                            @if ($hostRatingSummary->review_count > 0)
                                <span class="profile-host-stars" aria-hidden="true"><span>★★★★★</span><span class="profile-host-stars-fill" style="width: {{ min(100, max(0, (float) $hostRatingSummary->average_rating * 20)) }}%">★★★★★</span></span>
                                <span class="profile-host-rating-score">{{ number_format($hostRatingSummary->average_rating, 1) }}<span class="visually-hidden"> out of 5</span></span>
                            @else
                                <span class="profile-host-stars" aria-hidden="true">★★★★★</span>
                            @endif
                            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                        </a>
                    <div class="profile-actions">
                        @if ((int) auth()->id() === (int) $user->id)
                            <div class="dropdown profile-more-actions">
                                <button type="button" class="profile-more-button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More profile actions">
                                    <i class="fa-solid fa-ellipsis" aria-hidden="true"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="fa-solid fa-pen" aria-hidden="true"></i>Edit Profile</a>
                                    <button type="button" class="dropdown-item" data-profile-share data-profile-url="{{ route('profile.show', $user->id) }}">
                                        <i class="fa-solid fa-link" aria-hidden="true"></i>Copy profile link
                                    </button>
                                </div>
                            </div>
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
                                    <button type="button" class="dropdown-item" data-profile-share data-profile-url="{{ route('profile.show', $user->id) }}">
                                        <i class="fa-solid fa-link" aria-hidden="true"></i>Copy profile link
                                    </button>
                                @if ($isBlockedByViewer)
                                <form action="{{ route('messages.unblock', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item"><i class="fa-solid fa-unlock" aria-hidden="true"></i>{{ __('Unblock user') }}</button>
                                </form>
                            @else
                                <form action="{{ route('messages.block', $user) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-ban" aria-hidden="true"></i>{{ __('Block user') }}</button>
                                </form>
                                @endif
                                </div>
                            </div>
                        @endif
                    </div>
                        </div>
                        </div>
                        <p class="profile-bio">{{ $user->profile?->bio ?: __('Welcome to my profile!') }}</p>
                        @php
                            $isProfileOwner = (int) auth()->id() === (int) $user->id;
                            $canSeeLocation = $isProfileOwner || ($user->profile?->show_location ?? true);
                            $canSeeOccupation = $isProfileOwner || ($user->profile?->show_occupation ?? true);
                            $profileLocation = collect($canSeeLocation ? [$user->profile?->region, $user->profile?->country] : [])
                                ->map(fn ($value) => trim($value ?? ''))->filter(fn ($value) => $value !== '')->implode(', ');
                            $profileOccupation = $canSeeOccupation ? trim($user->profile?->occupation ?? '') : '';
                        @endphp
                        @if ($profileLocation !== '' || $profileOccupation !== '')
                            <ul class="profile-basic-info" aria-label="Profile information">
                                @if ($profileLocation !== '')
                                    <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i><span><span class="visually-hidden">Location: </span>{{ $profileLocation }}</span>
                                        @if ($isProfileOwner && !$user->profile->show_location)<span class="profile-private-label"><i class="fa-solid fa-lock" aria-hidden="true"></i>Only you</span>@endif
                                    </li>
                                @endif
                                @if ($profileOccupation !== '')
                                    <li><i class="fa-solid fa-briefcase" aria-hidden="true"></i><span><span class="visually-hidden">Occupation: </span>{{ $profileOccupation }}</span>
                                        @if ($isProfileOwner && !$user->profile->show_occupation)<span class="profile-private-label"><i class="fa-solid fa-lock" aria-hidden="true"></i>Only you</span>@endif
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                    <dl class="profile-stats">
                        @foreach (['Events Joined' => $user->attending_events_count, 'Events Hosted' => $user->events_owned_count, 'Followers' => $user->followers_count, 'Following' => $user->following_count] as $label => $count)
                            @php
                                $statUrl = match ($label) {
                                    'Events Joined' => (int) auth()->id() === (int) $user->id
                                        ? route('profile.show', ['id' => $user->id, 'tab' => 'history']).'#profile-activity' : null,
                                    'Events Hosted' => route('profile.show', ['id' => $user->id, 'tab' => 'event']).'#profile-activity',
                                    default => route('profile.show', ['id' => $user->id, 'connections' => strtolower($label), 'tab' => $activeTab]),
                                };
                            @endphp
                            <div>
                                <dt>@if ($statUrl)<a href="{{ $statUrl }}">{{ $label }}</a>@else{{ $label }}@endif</dt>
                                <dd>@if ($statUrl)<a href="{{ $statUrl }}" aria-label="{{ $label }}: {{ $count }}">{{ $count }}</a>@else{{ $count }}@endif</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>

            @if ($connections !== null)
                <dialog class="profile-connections" id="profile-connections-dialog" aria-labelledby="profile-connections-title">
                    <div class="profile-feed-heading">
                        <h2 id="profile-connections-title">{{ $connectionType === 'people' ? __('Find people') : ucfirst($connectionType) }} ({{ $connections->total() }})</h2>
                        <form method="dialog"><button type="submit" class="btn-close" aria-label="{{ __('Close list') }}"></button></form>
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
                            <li>{{ $connectionSearch !== '' ? __('No users found.') : __('No :type yet.', ['type' => $connectionType]) }}</li>
                        @endforelse
                    </ul>
                    {{ $connections->links() }}
                </dialog>
            @endif

            @if (!in_array($activeTab, ['feed', 'bookmark', 'history']))
                <nav class="profile-content-tabs profile-section-navigation" aria-label="User profile sections">
                    @foreach (['event' => 'Events', 'review' => 'Reviews', 'community' => 'Communities'] as $tab => $label)
                        @if ($tab === 'review')
                            <details class="profile-review-menu" id="profile-review-menu">
                                <summary @if($activeTab === 'review') aria-current="page" @endif>Reviews <i class="fa-solid fa-chevron-down ms-1" aria-hidden="true"></i></summary>
                                <div class="profile-review-options" aria-label="Review type">
                                    @foreach (['event' => 'Event reviews', 'host' => 'Host reviews'] as $type => $reviewLabel)
                                        <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'review', 'review_type' => $type]) }}#profile-activity"
                                            @if($activeTab === 'review' && $reviewType === $type) aria-current="page" @endif>
                                            <i class="fa-solid {{ $type === 'host' ? 'fa-user' : 'fa-calendar-days' }}" aria-hidden="true"></i>
                                            {{ $reviewLabel }}
                                        </a>
                                    @endforeach
                                    @if ((int) auth()->id() === (int) $user->id)
                                        <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'review', 'review_type' => 'written']) }}#profile-activity" @if($activeTab === 'review' && $reviewType === 'written') aria-current="page" @endif>
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>Written reviews
                                        </a>
                                    @endif
                                </div>
                            </details>
                        @else
                            <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => $tab]) }}" @if($activeTab === $tab) aria-current="page" @endif>{{ $label }}</a>
                        @endif
                    @endforeach
                </nav>
            @endif
            <section id="profile-activity" class="profile-feed" aria-label="Profile activity">
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
                    <div class="profile-hosted-header">
                        <div class="profile-feed-heading">
                            <h2>{{ __('Hosted events') }}</h2>
                            <span class="profile-event-count">{{ $events->count() }} {{ $events->count() === 1 ? __('event') : __('events') }}</span>
                        </div>
                        <nav class="profile-history-filters" aria-label="Filter hosted events">
                            @foreach (['all' => 'All', 'upcoming' => 'Upcoming', 'past' => 'Past'] + ((int) auth()->id() === (int) $user->id ? ['draft' => 'Drafts'] : []) as $value => $label)
                                <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'event', 'event_filter' => $value]) }}#profile-activity" @if ($eventFilter === $value) aria-current="page" @endif>{{ $label }}</a>
                            @endforeach
                        </nav>
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
                @if ((int) auth()->id() === (int) $user->id)
                <div data-profile-panel="bookmark" @if($activeTab !== 'bookmark') hidden @endif>
                    <div class="profile-feed-heading"><h2>{{ __('Bookmarks') }}</h2><span class="profile-history-count">{{ $bookmarkedEvents?->total() ?? 0 }} events</span></div>
                    @if ($bookmarkedEvents !== null)
                        <div class="profile-history-toolbar">
                            <nav class="profile-history-filters" aria-label="Filter bookmarked events">
                                @foreach (['upcoming' => 'Upcoming', 'all' => 'All'] as $value => $label)
                                    <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'bookmark', 'bookmark_filter' => $value, 'bookmark_sort' => $bookmarkSort]) }}#profile-activity" @if ($bookmarkFilter === $value) aria-current="page" @endif>{{ $label }}</a>
                                @endforeach
                            </nav>
                            <form method="GET" action="{{ route('profile.show', $user->id) }}#profile-activity" class="profile-history-sort">
                                <input type="hidden" name="tab" value="bookmark">
                                <input type="hidden" name="bookmark_filter" value="{{ $bookmarkFilter }}">
                                <label for="bookmark-sort" class="visually-hidden">Sort bookmarked events</label>
                                <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                                <select id="bookmark-sort" name="bookmark_sort" onchange="this.form.requestSubmit()">
                                    @foreach (['saved' => 'Recently saved', 'nearest' => 'Next events first'] as $value => $label)
                                        <option value="{{ $value }}" @selected($bookmarkSort === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <noscript><button type="submit">Apply</button></noscript>
                            </form>
                        </div>
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
                <div data-profile-panel="history" @if($activeTab !== 'history') hidden @endif>
                    <div class="profile-feed-heading"><h2>{{ __('Joined events') }}</h2><span class="profile-history-count">{{ $historyEvents?->total() ?? 0 }} events</span></div>
                    @if ($historyEvents !== null)
                        <div class="profile-history-toolbar">
                            <nav class="profile-history-filters" aria-label="Filter joined events">
                                @foreach (['upcoming' => 'Upcoming', 'pending' => 'Pending', 'past' => 'Past', 'all' => 'All'] as $value => $label)
                                    <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'history', 'history_filter' => $value, 'history_sort' => $historySort]) }}#profile-activity" @if ($historyFilter === $value) aria-current="page" @endif>{{ $label }}</a>
                                @endforeach
                            </nav>
                            <form method="GET" action="{{ route('profile.show', $user->id) }}#profile-activity" class="profile-history-sort">
                                <input type="hidden" name="tab" value="history">
                                <input type="hidden" name="history_filter" value="{{ $historyFilter }}">
                                <label for="history-sort" class="visually-hidden">Sort joined events by event date</label>
                                <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                                <select id="history-sort" name="history_sort" onchange="this.form.requestSubmit()">
                                    @foreach (['nearest' => 'Next events first', 'newest' => 'Newest first', 'oldest' => 'Oldest first'] as $value => $label)
                                        <option value="{{ $value }}" @selected($historySort === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <noscript><button type="submit">Apply</button></noscript>
                            </form>
                        </div>
                        <div class="event-grid profile-bookmark-grid">
                            @forelse ($historyEvents as $event)
                                <div>
                                    <p class="mb-2"><span class="profile-history-status {{ $event->pivot->status === 'pending' ? 'is-pending' : ($event->end_at->lt($historyNow) ? 'is-past' : 'is-upcoming') }}">{{ $event->pivot->status === 'pending' ? 'Pending approval' : ($event->end_at->lt($historyNow) ? 'Past event' : 'Upcoming') }}</span></p>
                                    @include('events.partials.card', ['event' => $event])
                                </div>
                            @empty
                                <p class="profile-events-empty">{{ __('No events match this filter.') }}</p>
                            @endforelse
                        </div>
                        {{ $historyEvents->links() }}
                    @else
                    <p class="profile-events-empty">{{ __('Event history is private.') }}</p>
                    @endif
                </div>
                @endif
                <div data-profile-panel="community" @if($activeTab !== 'community') hidden @endif>
                    <div class="profile-feed-heading"><h2>{{ __('Joined communities') }}</h2><span>{{ $joinedCommunities->total() }}</span></div>
                    <div class="event-grid profile-bookmark-grid">
                        @forelse ($joinedCommunities as $community)
                            <article class="card h-100 overflow-hidden profile-community-card">
                                <a href="{{ route('community.show', $community->id) }}">
                                    <img src="{{ $imageUrl($community->image, asset('images/onlylogo.jpg')) }}" alt="{{ $community->name }}" class="w-100" style="height: 160px; object-fit: cover;" loading="lazy">
                                </a>
                                <div class="card-body">
                                    <h3 class="h5"><a href="{{ route('community.show', $community->id) }}">{{ $community->name }}</a></h3>
                                    <p>{{ \Illuminate\Support\Str::limit($community->description, 150) }}</p>
                                    <a class="profile-community-link" href="{{ route('community.show', $community->id) }}">{{ __('View community') }} <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
                                </div>
                            </article>
                        @empty
                            <p class="profile-events-empty">{{ __('No joined communities yet.') }}</p>
                        @endforelse
                    </div>
                    {{ $joinedCommunities->links() }}
                </div>
                <div data-profile-panel="review" @if($activeTab !== 'review') hidden @endif>
                    @if ($activeTab === 'review')
                        @if ((int) auth()->id() === (int) $user->id)
                            <nav class="profile-review-direction" aria-label="Review direction">
                                <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'review', 'review_type' => 'event']) }}#profile-activity" @if ($reviewType !== 'written') aria-current="page" @endif>Received reviews</a>
                                <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'review', 'review_type' => 'written']) }}#profile-activity" @if ($reviewType === 'written') aria-current="page" @endif>Written reviews</a>
                            </nav>
                        @endif
                        @if ($reviewType === 'written')
                            @include('profile.partials.written-reviews')
                        @else
                        @php
                            $heading = $reviewType === 'host' ? 'Host reviews' : 'Event reviews';
                            $reviews = $reviewType === 'host' ? $hostReviews : $eventReviews;
                        @endphp
                            <section class="mb-4" aria-label="{{ $heading }}">
                                <div class="profile-feed-heading">
                                    <h2>{{ $heading }}</h2>
                                    <span>{{ $reviews->total() }} {{ $reviews->total() === 1 ? 'review' : 'reviews' }}</span>
                                </div>
                                @forelse ($reviews as $review)
                                    <article class="profile-review-card">
                                        <header class="profile-review-header">
                                            <span class="profile-review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->user?->name ?? '?', 0, 1)) }}</span>
                                            <div class="profile-review-author">
                                                @if ($review->user)
                                                    <a href="{{ route('profile.show', $review->user_id) }}">{{ $review->user->name }}</a>
                                                @else
                                                    <strong>Deleted user</strong>
                                                @endif
                                                @if ($review->created_at)
                                                    <time datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format('M j, Y') }}</time>
                                                @endif
                                            </div>
                                            <div class="profile-review-rating" aria-label="{{ $review->rating }} out of 5 stars">
                                                <span aria-hidden="true">{{ str_repeat('★', $review->rating) }}<span class="profile-review-unfilled">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                                                <strong aria-hidden="true">{{ $review->rating }}<small>/5</small></strong>
                                            </div>
                                        </header>
                                        @if ($review->comment)
                                            <p class="profile-review-comment">{{ $review->comment }}</p>
                                        @else
                                            <p class="profile-review-no-comment">Rating only</p>
                                        @endif
                                        <a class="profile-review-event" href="{{ route('events.show', $review->event_id) }}">
                                            <img src="{{ $imageUrl($review->event->image, asset('images/onlylogo.jpg')) }}" alt="" width="48" height="48" loading="lazy">
                                            <span><small>{{ $reviewType === 'host' ? 'Hosted event' : 'Reviewed event' }}</small><strong>{{ $review->event->title }}</strong></span>
                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </article>
                                @empty
                                    <p class="profile-events-empty">{{ $heading === 'Event reviews' ? 'No event reviews yet.' : 'No host reviews yet.' }}</p>
                                @endforelse
                                {{ $reviews->links() }}
                            </section>
                        @endif
                    @endif
                </div>
                <p class="profile-feedback" role="status" aria-live="polite"></p>
            </section>
        </div>
    </div>
    <style>
        .profile-review-card { padding: 22px; margin-bottom: 16px; border: 1px solid var(--profile-border); border-radius: 16px; background: #fff; color: var(--profile-ink); font-size: 14px; line-height: 1.7; }
        .profile-review-card a, .profile-review-card a:visited { color: var(--profile-ink); text-decoration: none; }
        .profile-review-card a:hover { color: #236d89; text-decoration: none; }
        .profile-review-card a:focus-visible { outline: 2px solid #267795; outline-offset: 3px; border-radius: 4px; }
        .profile-review-header { display: flex; align-items: center; gap: 12px; }
        .profile-review-avatar { display: grid; place-items: center; flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; background: #deeff7; color: #236d89; font-size: 18px; font-weight: 700; }
        .profile-review-author { flex: 1; min-width: 0; }
        .profile-review-author a, .profile-review-author strong { font-weight: 600; overflow-wrap: anywhere; }
        .profile-review-author time { display: block; color: var(--profile-muted); font-size: 12px; }
        .profile-review-rating { display: flex; flex-direction: column; align-items: flex-end; flex-shrink: 0; line-height: 1.4; }
        .profile-review-rating > span { color: #b57b12; letter-spacing: 2px; }
        .profile-review-unfilled { color: #dfe8ed; }
        .profile-review-rating strong { font-size: 16px; }
        .profile-review-rating small { color: var(--profile-muted); font-size: 11px; font-weight: 400; margin-left: 3px; }
        .profile-review-comment { margin: 20px 0; font-size: 15px; line-height: 1.8; white-space: pre-line; overflow-wrap: anywhere; }
        .profile-review-no-comment { margin: 20px 0; color: var(--profile-muted); }
        .profile-review-event { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f5f9fb; border-radius: 10px; }
        .profile-review-event:hover { background: #edf5f9; }
        .profile-review-event img { flex-shrink: 0; border-radius: 8px; object-fit: cover; }
        .profile-review-event > span { flex: 1; min-width: 0; }
        .profile-review-event small { display: block; color: var(--profile-muted); font-size: 11px; }
        .profile-review-event strong { display: block; font-size: 13px; font-weight: 600; overflow-wrap: anywhere; }
        .profile-review-event > i { color: #236d89; font-size: 12px; }
        @media (max-width: 420px) { .profile-review-card { padding: 16px; } .profile-review-header { flex-wrap: wrap; } .profile-review-rating { flex-direction: row; align-items: center; gap: 8px; width: 100%; padding-left: 56px; } }
        .profile-section-navigation { overflow: visible; }
        .profile-review-menu { position: relative; }
        .profile-review-menu summary { padding: 10px 18px; border-radius: 8px; color: #647580; font-size: 14px; font-weight: 600; white-space: nowrap; cursor: pointer; list-style: none; }
        .profile-review-menu summary::-webkit-details-marker { display: none; }
        .profile-review-menu summary:hover, .profile-review-menu[open] summary { background: #f1f7fa; color: #236d89; }
        .profile-review-menu summary[aria-current="page"] { background: #deeff7; color: #236d89; }
        .profile-review-menu summary:focus-visible { outline: 3px solid #267795; outline-offset: 2px; }
        .profile-review-options { position: absolute; top: 100%; left: 0; z-index: 30; min-width: 185px; padding: 6px; border: 1px solid #dfe8ed; border-radius: 12px; background: #fff; box-shadow: 0 8px 24px rgb(0 0 0 / 10%); }
        .profile-review-options a { display: flex; align-items: center; gap: 10px; }
        @media (max-width: 420px) { .profile-section-navigation { gap: 2px; } .profile-section-navigation > a, .profile-review-menu summary { padding-inline: 10px; } }
    </style>
    <div class="profile-share-feedback" hidden>
        <div class="profile-share-message">
            <p id="profile-share-status" role="status" aria-live="polite"></p>
            <button type="button" class="profile-share-dismiss" aria-label="Dismiss notification">×</button>
        </div>
        <input type="text" id="profile-share-url" value="{{ route('profile.show', $user->id) }}" aria-label="Profile link to copy" readonly hidden>
    </div>
    <script>
        let profileShareTimer;
        document.querySelector('.profile-share-dismiss').addEventListener('click', () => {
            clearTimeout(profileShareTimer);
            document.querySelector('.profile-share-feedback').hidden = true;
        });
        document.querySelectorAll('[data-profile-share]').forEach(profileShareButton => {
            profileShareButton.addEventListener('click', async () => {
                const feedback = document.querySelector('.profile-share-feedback');
                const status = document.getElementById('profile-share-status');
                const urlInput = document.getElementById('profile-share-url');
                clearTimeout(profileShareTimer);
                profileShareButton.disabled = true;
                try {
                    await navigator.clipboard.writeText(profileShareButton.dataset.profileUrl);
                    urlInput.hidden = true;
                    feedback.hidden = false;
                    status.textContent = 'Profile link copied.';
                    profileShareTimer = setTimeout(() => { feedback.hidden = true; }, 3500);
                } catch {
                    feedback.hidden = false;
                    status.textContent = 'Select and copy this profile link:';
                    urlInput.hidden = false;
                    urlInput.focus();
                    urlInput.select();
                } finally {
                    profileShareButton.disabled = false;
                }
            });
        });
        const reviewMenu = document.getElementById('profile-review-menu');
        if (reviewMenu) {
            reviewMenu.addEventListener('pointerenter', event => {
                if (event.pointerType === 'mouse') reviewMenu.open = true;
            });
            reviewMenu.addEventListener('pointerleave', event => {
                if (event.pointerType === 'mouse' && !reviewMenu.contains(document.activeElement)) reviewMenu.open = false;
            });
            reviewMenu.addEventListener('focusout', event => {
                if (!reviewMenu.contains(event.relatedTarget)) reviewMenu.open = false;
            });
            reviewMenu.addEventListener('keydown', event => {
                if (event.key === 'Escape') {
                    reviewMenu.open = false;
                    reviewMenu.querySelector('summary').focus();
                }
            });
            document.addEventListener('click', event => {
                if (!reviewMenu.contains(event.target)) reviewMenu.open = false;
            });
        }
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
