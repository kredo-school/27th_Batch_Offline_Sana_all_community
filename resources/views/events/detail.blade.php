@extends('layouts.app-with-event-sidebar')

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
    </style>

    @php
        $category = $event->categories->first();
        $joined = $event->participants()->where('status', 'approved')->count();
        $percent = $event->capacity ? min(100, round(($joined / $event->capacity) * 100)) : 0;
        $daysLeft = (int) floor(now()->diffInDays($event->join_till, false));
        $isOwner = auth()->id() === $event->owner_id;
        $visibilityLabels = [
            'public' => 'All users',
            'members' => 'Logged-in users',
            'followers' => 'Followers only',
        ];
        $dateText =
            $event->start_at->format('M j (D) H:i') .
            ' ~ ' .
            ($event->start_at->isSameDay($event->end_at)
                ? $event->end_at->format('H:i')
                : $event->end_at->format('M j (D) H:i'));
        $priceText = (float) $event->price == 0 ? 'Free' : number_format((float) $event->price) . ' PHP';
    @endphp

    <div class="container py-4" style="max-width: 1000px;">

        {{-- back --}}
        <a href="{{ route('events.index') }}" class="text-decoration-none text-dark d-inline-block mb-3">&larr; back to
            events</a>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-4">

            {{-- ===== left: main ===== --}}
            <div class="col-lg-8">

                {{-- cover image --}}
                <div class="event-cover">
                    @if ($event->image)
                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}">
                    @else
                        <div class="event-cover-empty">No image</div>
                    @endif

                    <span class="event-deadline">
                        @if ($daysLeft > 0)
                            closes in {{ $daysLeft }} days
                        @elseif ($daysLeft === 0)
                            closes today
                        @else
                            registration closed
                        @endif
                    </span>
                </div>

                {{-- basics --}}
                <div class="event-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        @if ($category)
                            <span class="event-category">{{ $category->name }}</span>
                        @endif
                        <span class="event-help">{{ $visibilityLabels[$event->visibility] ?? $event->visibility }}</span>
                    </div>

                    <h2 class="fw-normal mb-3">{{ $event->title }}</h2>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">Date</div>
                                <div class="event-info-value">{{ $dateText }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">Location</div>
                                <div class="event-info-value">{{ $event->location }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">Fee</div>
                                <div class="event-info-value">{{ $priceText }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="event-info-box">
                                <div class="event-info-label">Capacity</div>
                                <div class="event-info-value">{{ $event->capacity }} people</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="event-info-box">
                                <div class="event-info-label">Registration closes</div>
                                <div class="event-info-value">{{ $event->join_till->format('M j (D) H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-3">
                        <span class="event-help">Language</span>
                        <span class="event-lang {{ $event->language === 'en' ? 'active-en' : '' }}">EN English</span>
                        <span class="event-lang {{ $event->language === 'ja' ? 'active-ja' : '' }}">JA Japanese</span>
                    </div>
                </div>

                {{-- description --}}
                <div class="event-card">
                    <h5>Description</h5>
                    <p class="mb-0" style="white-space: pre-line;">{{ $event->description }}</p>
                </div>

                {{-- comments --}}
                <div class="event-card">
                    <h5>Comments({{ $event->comments->count() }})</h5>

                    @auth
                        <form action="{{ route('events.comments.store', $event) }}" method="POST" class="d-flex gap-2 mb-1">
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

                    <div class="mt-3">
                        @foreach ($event->comments as $comment)
                            @php
                                $canDeleteComment =
                                    auth()->check() &&
                                    (auth()->id() === $event->owner_id || auth()->id() === $comment->user_id);
                            @endphp

                            <div class="d-flex gap-2 mb-3">
                                <div class="event-avatar">{{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}</div>
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
                    <div class="fs-4">{{ $priceText }}</div>
                    <div class="event-help mb-1">{{ $joined }}/{{ $event->capacity }} joined</div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar" style="width: {{ $percent }}%;"></div>
                    </div>
                    @if ($isOwner)
                        <a href="{{ route('events.edit', $event) }}" class="btn btn-outline-primary w-100 mb-2">Edit
                            event</a>
                    @endif
                    @unless ($isOwner)
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
                                <div class="alert alert-secondary text-center small py-2 mb-0">Your request was not accepted.</div>
                            @endif
                        @endguest
                    @endunless
                </div>

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
                            <div>{{ $event->owner->name ?? 'Unknown' }}</div>
                            <div class="event-help">host</div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('events.participants', $event) }}" class="btn btn-primary btn-sm">See all
                    participants</a>
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
@endsection
