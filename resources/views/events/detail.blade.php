@extends('layouts.app')

@section('sidebar', $event->community_id ? 'community' : 'events')

@section('content')
    <style>
        .event-card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .event-cover {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #e9ecef;
            aspect-ratio: 16 / 9;
            margin-bottom: 16px;
        }

        .event-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-cover-empty {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888780;
        }

        .event-deadline {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #fff;
            color: #e24b4a;
            border: 1px solid #e24b4a;
            border-radius: 999px;
            font-size: 12px;
            padding: 2px 10px;
        }

        .event-category {
            background: #c9e58b;
            border-radius: 999px;
            padding: 2px 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .event-info-box {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 10px 14px;
            height: 100%;
        }

        .event-info-label {
            color: #888780;
            font-size: 11px;
        }

        .event-info-value {
            font-size: 14px;
            font-weight: 500;
        }

        .event-lang {
            border: 1px solid #ccc;
            border-radius: 999px;
            padding: 1px 10px;
            font-size: 12px;
            color: #aaa;
        }

        .event-lang.active-en {
            background: #e6f1fb;
            border-color: #3d8fd6;
            color: #3d8fd6;
        }

        .event-lang.active-ja {
            background: #fbe6e6;
            border-color: #e24b4a;
            color: #e24b4a;
        }

        .event-help {
            color: #888780;
            font-size: 12px;
        }

        .event-map {
            width: 100%;
            height: 180px;
            border: 0;
            border-radius: 8px;
        }

        .event-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e6f1fb;
            color: #3d8fd6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-action-avatar {
            cursor: pointer;
        }

        .user-action-menu {
            position: fixed;
            z-index: 1080;
            display: none;
            width: 250px;
            padding: 10px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 4px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 28px;
            color: #ddd;
            cursor: pointer;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #f5b301;
        }

        .review-stars {
            color: #f5b301;
            letter-spacing: 1px;
        }
    </style>

    @php
        $category = $event->categories->first();
        $joined = $event->participants()->where('status', 'approved')->count();
        $percent = $event->capacity ? min(100, round(($joined / $event->capacity) * 100)) : 0;
        $daysLeft = (int) floor(now()->diffInDays($event->join_till, false));
        $isOwner = auth()->id() === $event->owner_id;
        $visibilityLabels = [
            'public' => __('All users'),
            'members' => __('Logged-in users'),
            'followers' => __('Followers only'),
        ];
        $dateText =
            $event->start_at->format('M j (D) H:i') .
            ' ~ ' .
            ($event->start_at->isSameDay($event->end_at)
                ? $event->end_at->format('H:i')
                : $event->end_at->format('M j (D) H:i'));
        $priceText = (float) $event->price == 0 ? 'Free' : number_format((float) $event->price) . ' PHP';
        $isCancelled = $event->status === 'cancelled';
        $hasEnded = $event->end_at->isPast();
        $myReview = auth()->check() ? $event->reviews->firstWhere('user_id', auth()->id()) : null;
        $canReview =
            $hasEnded && !$isCancelled && $myParticipation && $myParticipation->status === 'approved' && !$myReview;
        $reviewCount = $event->reviews->count();
        $averageRating = $reviewCount ? round($event->reviews->avg('rating'), 1) : 0;
        $myHostReview = auth()->check() ? $event->userReviews->firstWhere('user_id', auth()->id()) : null;
        $canReviewHost =
            $hasEnded &&
            !$isCancelled &&
            !$isOwner &&
            $myParticipation &&
            $myParticipation->status === 'approved' &&
            !$myHostReview;
    @endphp

    <div class="container py-4" style="max-width: 1000px;">

        {{-- back（コミュニティ内のイベントはそのコミュニティへ戻る） --}}
        @if ($event->community)
            <a href="{{ route('community.show', $event->community) }}"
                class="text-decoration-none text-dark d-inline-block mb-3">&larr; back to
                {{ $event->community->name }}</a>
        @else
            <a href="{{ route('events.index') }}" class="text-decoration-none text-dark d-inline-block mb-3">&larr; {{ __('Back to') }}
                {{ __('Events') }}</a>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if ($isCancelled)
            <div class="alert alert-danger">
                <strong>{{ __('This event has been cancelled.') }}</strong>
                @if ($event->cancel_reason)
                    <div class="mt-1" style="white-space: pre-line;">{{ __('Reason') }}: {{ $event->cancel_reason }}</div>
                @endif
            </div>
        @endif

        <div class="row g-4">

            {{-- ===== left: main ===== --}}
            <div class="col-lg-8">

                {{-- cover image --}}
                <div class="event-cover">
                    @if ($event->image)
                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}">
                    @else
                        <div class="event-cover-empty">{{ __('No image') }}</div>
                    @endif

                    <span class="event-deadline">
                        @if ($isCancelled)
                            {{ __('Cancelled') }}
                        @elseif ($daysLeft > 0)
                            {{ __('Closes in') }} {{ $daysLeft }} {{ __('days') }}
                        @elseif ($daysLeft === 0)
                            {{ __('Closes today') }}
                        @else
                            {{ __('Registration closed') }}
                        @endif
                    </span>
                </div>

                {{-- basics --}}
                <div class="event-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if ($category)
                            <span class="event-category">{{ $category->localized_name }}</span>
                        @endif
                        <span class="event-help">{{ $visibilityLabels[$event->visibility] ?? $event->visibility }}</span>
                    </div>

                    <h2 class="fw-normal mb-3">{{ $event->title }}</h2>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">{{ __('Date') }}</div>
                                <div class="event-info-value">{{ $dateText }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">{{ __('Location') }}</div>
                                <div class="event-info-value">{{ $event->location }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">{{ __('Fee') }}</div>
                                <div class="event-info-value">{{ $priceText }}</div>
                                @if ($feeNote)
                                    <div class="event-help">{{ $feeNote }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">{{ __('Capacity') }}</div>
                                <div class="event-info-value">{{ $event->capacity }} {{ __('people') }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="event-info-box">
                                <div class="event-info-label">{{ __('Registration closes') }}</div>
                                <div class="event-info-value">{{ $event->join_till->format('M j (D) H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-3">
                        <span class="event-help">{{ __('Language') }}</span>
                        <span class="event-lang {{ $event->language === 'en' ? 'active-en' : '' }}">EN {{ __('English') }}</span>
                        <span class="event-lang {{ $event->language === 'ja' ? 'active-ja' : '' }}">JA {{ __('Japanese') }}</span>
                    </div>
                </div>

                {{-- description --}}
                <div class="event-card">
                    <h5>{{ __('Description') }}</h5>
                    <p class="mb-0" style="white-space: pre-line;">{{ $event->description }}</p>
                </div>
                {{-- reviews (イベント終了後だけ) --}}
                @if ($hasEnded)
                    <div class="event-card">
                        <h5 class="mb-3">{{ __('Reviews') }}({{ $reviewCount }})</h5>

                        {{-- 平均と評価ごとの棒グラフ --}}
                        <div class="d-flex align-items-center gap-4 mb-3">
                            <div class="text-center">
                                <div class="fs-2">{{ $averageRating }}</div>
                                <div class="review-stars">
                                    {{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                @for ($star = 5; $star >= 1; $star--)
                                    @php
                                        $count = $event->reviews->where('rating', $star)->count();
                                        $percent = $reviewCount ? round(($count / $reviewCount) * 100) : 0;
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span style="width: 24px;">{{ $star }}★</span>
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $percent }}%;">
                                            </div>
                                        </div>
                                        <span style="width: 24px;" class="text-end">{{ $count }}</span>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- 投稿フォーム(参加した人だけ) --}}
                        @if ($canReview)
                            <form action="{{ route('events.reviews.store', $event) }}" method="POST"
                                class="border rounded p-3 mb-3">
                                @csrf
                                <div class="event-info-label mb-1">How was this event?</div>
                                <div class="star-rating mb-2">
                                    @for ($star = 5; $star >= 1; $star--)
                                        <input type="radio" name="rating" id="star{{ $star }}"
                                            value="{{ $star }}" @checked(old('rating') == $star) required>
                                        <label for="star{{ $star }}">★</label>
                                    @endfor
                                </div>
                                @error('rating')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                <textarea name="comment" rows="3" maxlength="500" class="form-control mb-2"
                                    placeholder="Share your experience (optional)">{{ old('comment') }}</textarea>
                                @error('comment')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                <button type="submit" class="btn btn-primary">Post review</button>
                            </form>
                        @elseif ($myReview)
                            <p class="event-help">You have already reviewed this event.</p>
                        @endif

                        {{-- レビュー一覧 --}}
                        @forelse ($event->reviews as $review)
                            <div class="d-flex gap-2 mb-3">
                                <a href="{{ route('profile.show', $review->user_id) }}"
                                    class="event-avatar user-action-avatar text-decoration-none"
                                    data-user-id="{{ $review->user_id }}"
                                    data-user-name="{{ $review->user->username ?: $review->user->name }}"
                                    aria-label="{{ __('View profile') }}">{{ strtoupper(substr($review->user->name ?? '?', 0, 1)) }}</a>
                                <div>
                                    <div>
                                        <strong>{{ $review->user->name ?? 'Unknown' }}</strong>
                                        <span
                                            class="review-stars ms-1">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                        <span class="event-help ms-1">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if ($review->comment)
                                        <div style="white-space: pre-line;">{{ $review->comment }}</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="event-help mb-0">No reviews yet.</p>
                        @endforelse
                    </div>
                @endif

                {{-- comments --}}
                <div class="event-card">
                    <h5>Comments({{ $event->comments->count() }})</h5>

                    @if (!$hasEnded)
                        @auth
                            <form action="{{ route('events.comments.store', $event) }}" method="POST"
                                class="d-flex gap-2 mb-1">
                                @csrf
                                <input type="text" name="content" class="form-control @error('content') is-invalid @enderror"
                                    placeholder="write a comment....." maxlength="500" required>
                                <button type="submit" class="btn btn-primary">Post</button>
                            </form>
                            @error('content')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        @else
                            <p class="event-help">Please log in to write a comment.</p>
                        @endauth
                    @else
                        <p class="event-help">Comments are closed because this event has ended.</p>
                    @endif

                    <div class="mt-3">
                        @foreach ($event->comments as $comment)
                            @php
                                $canDeleteComment =
                                    auth()->check() &&
                                    (auth()->id() === $event->owner_id || auth()->id() === $comment->user_id);
                            @endphp

                            <div class="d-flex gap-2 mb-3">
                                <a href="{{ route('profile.show', $comment->user_id) }}"
                                    class="event-avatar user-action-avatar text-decoration-none"
                                    data-user-id="{{ $comment->user_id }}"
                                    data-user-name="{{ $comment->user->username ?: $comment->user->name }}"
                                    aria-label="{{ __('View profile') }}">{{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}</a>
                                <div class="flex-grow-1">
                                    <div>
                                        <strong>{{ $comment->user->name ?? 'Unknown' }}</strong>
                                        @if ($comment->user_id === $event->owner_id)
                                            <span class="small text-primary ms-1">Host</span>
                                        @endif
                                        <span class="event-help ms-1">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div style="white-space: pre-line;">{{ $comment->content }}</div>
                                </div>
                                @if ($canDeleteComment)
                                    <button type="button" class="btn btn-link text-secondary p-0 align-self-start"
                                        data-bs-toggle="modal" data-bs-target="#deleteComment{{ $comment->id }}"
                                        aria-label="Delete comment">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                @endif
                            </div>
                            @if ($canDeleteComment)
                                <div class="modal fade" id="deleteComment{{ $comment->id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-danger">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Delete the comment</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-danger mb-3">Are you sure you want to delete this comment?
                                                </p>
                                                <div class="mb-2">
                                                    <div class="event-info-label">User name</div>
                                                    <div class="event-info-value">{{ $comment->user->name ?? 'Unknown' }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="event-info-label">Detail</div>
                                                    <div class="event-info-value" style="white-space: pre-line;">
                                                        {{ $comment->content }}</div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('events.comments.destroy', [$event, $comment]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ===== right: side ===== --}}
            <div class="col-lg-4">

                {{-- fee & join --}}
                <div class="event-card">
                    <div class="{{ $hasPrice ? 'fs-4' : 'fs-5' }}">{{ $priceText }}</div>
                    @if ($feeNote)
                        <div class="event-help mb-1">{{ $feeNote }}</div>
                    @endif
                    <div class="event-help mb-1">{{ $joined }}/{{ $event->capacity }} joined</div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ $percent }}%;"></div>
                    </div>
                    @if ($isCancelled)
                        <div class="alert alert-secondary text-center small py-2 mb-0">This event has been cancelled.</div>
                    @elseif ($isOwner)
                        <a href="{{ route('events.edit', $event) }}" class="btn btn-outline-primary w-100 mb-2">Edit
                            event</a>
                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal"
                            data-bs-target="#cancelEventModal">
                            Cancel the event
                        </button>
                    @elseif ($hasEnded)
                        <div class="alert alert-secondary text-center small py-2 mb-0">This event has ended.</div>
                    @else
                        @guest
                            <a href="{{ route('login') }}" class="btn btn-primary w-100">Log in to join</a>
                        @else
                            @if (!$myParticipation)
                                <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                                    data-bs-target="#joinModal">
                                    Request to join
                                </button>
                                @if ($event->approval_required)
                                    <div class="event-help text-center mt-2">Host approval required</div>
                                @endif
                            @elseif ($myParticipation->status === 'pending')
                                <div class="alert alert-warning text-center small py-2 mb-2">Waiting for host approval</div>
                                <form action="{{ route('events.join.cancel', $event) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">Cancel request</button>
                                </form>
                            @elseif ($myParticipation->status === 'approved')
                                <div class="alert alert-success text-center small py-2 mb-2">You're joined!</div>
                                <form action="{{ route('events.join.cancel', $event) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">Cancel</button>
                                </form>
                            @else
                                <div class="alert alert-secondary text-center small py-2 mb-0">Your request was not accepted.
                                </div>
                            @endif
                        @endguest
                    @endif
                </div>

                {{-- コミュニティ内のイベントはオーナーだけここから削除できる（中止済み・終了後も可） --}}
                @if ($isOwner && $event->community_id)
                    <button type="button" class="btn btn-outline-danger w-100 mb-3" data-bs-toggle="modal"
                        data-bs-target="#deleteEventModal{{ $event->id }}">
                        <i class="fa-regular fa-trash-can me-1"></i> Delete event
                    </button>
                @endif

                {{-- map --}}
                <div class="event-card">
                    <iframe class="event-map" loading="lazy"
                        src="https://maps.google.com/maps?q={{ urlencode($event->location) }}&output=embed"></iframe>
                    <div class="mt-2">{{ $event->location }}</div>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($event->location) }}"
                        target="_blank" class="small">Get Directions &#8599;</a>
                </div>

                {{-- host --}}
                <div class="event-card">
                    <div class="event-help mb-2">Hosted by</div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="event-avatar">{{ strtoupper(substr($event->owner->name ?? '?', 0, 1)) }}</div>
                        <div>
                            @if ($event->owner)
                                <a href="{{ route('profile.show', $event->owner->id) }}"
                                    class="text-decoration-none text-reset fw-semibold">
                                    {{ $event->owner->name }}
                                </a>
                            @else
                                <div>Unknown</div>
                            @endif
                            <div class="event-help">
                                host
                                @if ($hostReviewCount)
                                    <span class="review-stars ms-1">★</span> {{ $hostRating }} ({{ $hostReviewCount }})
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if ($hasEnded && !$isOwner)
                    @if ($canReviewHost)
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#hostReviewModal">
                            Review the host
                        </button>
                    @elseif ($myHostReview)
                        <p class="event-help mb-0">You have reviewed the host. Thank you!</p>
                    @elseif (auth()->guest())
                        <p class="event-help mb-0">Log in to review the host.</p>
                    @endif
                @else
                    <a href="{{ route('events.participants', $event) }}" class="btn btn-primary btn-sm">See all
                        participants</a>
                @endif
            </div>
        </div>
    </div>
    @auth
        <div class="modal fade" id="joinModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('events.join', $event) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Do you want to join this event?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <div class="event-info-label">Event name</div>
                                <div class="event-info-value">{{ $event->title }}</div>
                            </div>
                            <div class="mb-2">
                                <div class="event-info-label">Date</div>
                                <div class="event-info-value">{{ $dateText }}</div>
                            </div>
                            <div class="mb-2">
                                <div class="event-info-label">Fee</div>
                                <div class="event-info-value">{{ $priceText }}</div>
                            </div>
                            @if ($event->approval_required)
                                <p class="event-help mb-0 mt-3">The host needs to approve your request before you can join.</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Send request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endauth
    @if ($isOwner && $event->community_id)
        @include('events.partials.delete-event-modal')
    @endif
    @if ($isOwner && !$isCancelled)
        <div class="modal fade" id="cancelEventModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger">
                    <form action="{{ route('events.cancel', $event) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Cancel the event</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-danger mb-3">Are you sure you want to cancel this event? This cannot be undone.
                            </p>
                            <div class="mb-2">
                                <div class="event-info-label">Event name</div>
                                <div class="event-info-value">{{ $event->title }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="event-info-label">Date</div>
                                <div class="event-info-value">{{ $dateText }}</div>
                            </div>
                            <label for="cancel_reason" class="event-info-label">Reason</label>
                            <textarea name="cancel_reason" id="cancel_reason" rows="3" maxlength="500" required
                                class="form-control @error('cancel_reason') is-invalid @enderror"
                                placeholder="Please tell the participants why the event is cancelled.">{{ old('cancel_reason') }}</textarea>
                            @error('cancel_reason')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Back</button>
                            <button type="submit" class="btn btn-danger">Cancel the event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @if ($canReviewHost)
        <div class="modal fade" id="hostReviewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('events.host-reviews.store', $event) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Review the host</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="event-avatar">{{ strtoupper(substr($event->owner->name ?? '?', 0, 1)) }}</div>
                                <div>
                                    <div>{{ $event->owner->name ?? 'Unknown' }}</div>
                                    <div class="event-help">host of {{ $event->title }}</div>
                                </div>
                            </div>

                            <div class="event-info-label mb-1">How was the host?</div>
                            <div class="star-rating mb-2">
                                @for ($star = 5; $star >= 1; $star--)
                                    <input type="radio" name="host_rating" id="hostStar{{ $star }}"
                                        value="{{ $star }}" @checked(old('host_rating') == $star) required>
                                    <label for="hostStar{{ $star }}">★</label>
                                @endfor
                            </div>
                            @error('host_rating')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror

                            <textarea name="host_comment" rows="3" maxlength="500" class="form-control"
                                placeholder="Share your experience with the host (optional)">{{ old('host_comment') }}</textarea>
                            @error('host_comment')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Back</button>
                            <button type="submit" class="btn btn-primary">Post review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    @auth
        <div id="user-action-menu" class="user-action-menu " role="menu" aria-hidden="true">
            <a id="user-action-profile" class="btn btn-link d-block text-start px-0 text-decoration-none text-dark" href="#">
                {{ __('View profile') }}
            </a>
            <button id="user-action-message" type="button" class="btn btn-link d-block text-start px-0 js-open-message-chat text-decoration-none text-dark"
                data-message-chat-url="" data-message-chat-send-url="" data-message-chat-name="">
                {{ __('Send message') }}
            </button>
            <form id="user-action-block-form" method="POST" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-link d-block text-start text-warning px-0 text-decoration-none">
                    {{ __('Block user') }}
                </button>
            </form>
            <form id="user-action-report-form" method="POST">
                @csrf
                <label for="user-action-report-detail" class="small text-muted">{{ __('Report user') }}</label>
                <textarea id="user-action-report-detail" name="detail" class="form-control form-control-sm mb-2"
                    rows="2" maxlength="2000" required placeholder="{{ __('Reason for report') }}"></textarea>
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">{{ __('Report to admin') }}</button>
            </form>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const menu = document.getElementById('user-action-menu');
                const profileLink = document.getElementById('user-action-profile');
                const messageButton = document.getElementById('user-action-message');
                const blockForm = document.getElementById('user-action-block-form');
                const reportForm = document.getElementById('user-action-report-form');
                const userUrl = (template, userId) => template.replace('__USER__', userId);
                const profileTemplate = @json(route('profile.show', '__USER__'));
                const messageTemplate = @json(route('messages.conversation', '__USER__'));
                const sendTemplate = @json(route('messages.send', '__USER__'));
                const blockTemplate = @json(route('messages.block', '__USER__'));
                const reportTemplate = @json(route('users.report', '__USER__'));

                document.querySelectorAll('.user-action-avatar').forEach(function (avatar) {
                    avatar.addEventListener('contextmenu', function (event) {
                        event.preventDefault();
                        const userId = avatar.dataset.userId;
                        if (String(userId) === String(@json(auth()->id()))) {
                            return;
                        }

                        profileLink.href = userUrl(profileTemplate, userId);
                        messageButton.dataset.messageChatUrl = userUrl(messageTemplate, userId);
                        messageButton.dataset.messageChatSendUrl = userUrl(sendTemplate, userId);
                        messageButton.dataset.messageChatName = avatar.dataset.userName || '';
                        blockForm.action = userUrl(blockTemplate, userId);
                        reportForm.action = userUrl(reportTemplate, userId);
                        menu.style.display = 'block';
                        menu.style.left = Math.min(event.clientX, window.innerWidth - menu.offsetWidth - 8) + 'px';
                        menu.style.top = Math.min(event.clientY, window.innerHeight - menu.offsetHeight - 8) + 'px';
                        menu.setAttribute('aria-hidden', 'false');
                    });
                });

                document.addEventListener('click', function (event) {
                    if (!menu.contains(event.target)) {
                        menu.style.display = 'none';
                        menu.setAttribute('aria-hidden', 'true');
                    }
                });
            });
        </script>
    @endauth
@endsection
