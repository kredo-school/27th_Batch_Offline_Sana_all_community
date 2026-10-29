@extends('layouts.app')

@section('title', 'Event Details')

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <a href="{{ route('admin.events') }}" class="small text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Back to events
                </a>

                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
                    <div>
                        <h1 class="h3 mb-2">{{ $event->title }}</h1>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-{{ $event->status === 'published' ? 'success' : 'secondary' }}">
                                {{ ucfirst($event->status) }}
                            </span>
                            <span class="badge bg-{{ $event->visibility === 'public' ? 'primary' : 'dark' }}">
                                {{ ucfirst($event->visibility) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-pen me-1" aria-hidden="true"></i>Edit event
                    </a>
                </div>

                @if ($event->image)
                    <img class="img-fluid rounded mb-4" src="{{ asset('storage/'.$event->image) }}"
                        alt="{{ $event->title }}" style="max-height: 360px; width: 100%; object-fit: cover;">
                @endif

                <h2 class="h5">Description</h2>
                <p class="mb-4" style="white-space: pre-wrap;">{{ $event->description ?: 'No description provided.' }}</p>

                <dl class="row mb-0">
                     @if ($event->owner->avatar)
                        @php
                            $avatarPath = $event->owner->avatar;
                            $avatarUrl = str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                ? $avatarPath : asset('storage/'.$avatarPath);
                        @endphp
                        <dt class="col-sm-4">
                        <img src="{{ $avatarUrl }}" alt="{{ $event->owner->username }}" class="rounded-circle">
                        </dt>
                        <dd class="col-sm-8">{{ $event->owner?->username ?? 'Unknown' }}</dd> 
                    @else
                        <dt class="col-sm-4">Owner</dt>
                        <dd class="col-sm-8">{{ $event->owner?->username ?? 'Unknown' }}</dd>
                    @endif
                    

                    <dt class="col-sm-4">Categories</dt>
                    <dd class="col-sm-8">
                        @forelse ($event->categories as $category)
                            <span class="badge bg-success me-1">{{ $category->name }}</span>
                        @empty
                            -
                        @endforelse
                    </dd>

                    <dt class="col-sm-4">Community</dt>
                    <dd class="col-sm-8">{{ $event->community?->name ?? '-' }}</dd>

                    <dt class="col-sm-4">Starts</dt>
                    <dd class="col-sm-8">{{ $event->start_at?->format('M j, Y g:i A') ?? '-' }}</dd>

                    <dt class="col-sm-4">Join till</dt>
                    <dd class="col-sm-8">{{ $event->join_till?->format('M j, Y g:i A') ?? '-' }}</dd>

                    <dt class="col-sm-4">Ends</dt>
                    <dd class="col-sm-8">{{ $event->end_at?->format('M j, Y g:i A') ?? '-' }}</dd>

                    <dt class="col-sm-4">Location</dt>
                    <dd class="col-sm-8">{{ $event->location ?: '-' }}</dd>

                    <dt class="col-sm-4">Region</dt>
                    <dd class="col-sm-8">{{ $event->region ?: '-' }}</dd>

                    <dt class="col-sm-4">Language</dt>
                    <dd class="col-sm-8">{{ strtoupper($event->language) }}</dd>

                    <dt class="col-sm-4">Capacity</dt>
                    <dd class="col-sm-8">{{ $event->capacity ?? 'Unlimited' }}</dd>

                    <dt class="col-sm-4">Price</dt>
                    <dd class="col-sm-8">{{ number_format((float) $event->price, 2) }}</dd>

                    <dt class="col-sm-4">Approval required</dt>
                    <dd class="col-sm-8">{{ $event->approval_required ? 'Yes' : 'No' }}</dd>

                    <dt class="col-sm-4">Created</dt>
                    <dd class="col-sm-8">{{ $event->created_at?->format('M j, Y g:i A') ?? '-' }}</dd>
                </dl>
            </div>
            <section class="mt-4 ms-5 me-5" aria-labelledby="event-reviews-heading">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <h2 id="event-reviews-heading" class="h5 mb-0">Reviews</h2>
                    <span class="text-secondary small">{{ $event->reviews->count() }} total</span>
                </div>

                @if ($event->reviews->isNotEmpty())
                <p class="fw-bold mb-2">
                    Average rating: {{ number_format($event->reviews->avg('rating'), 1) }} / 5
                </p>
                @php
                    $ratingCounts = $event->reviews->countBy('rating');
                    $ratingTotal = $event->reviews->count();
                @endphp
                <div class="mb-3" aria-label="Rating distribution">
                     @if ($event->owner->avatar)
                        @php
                            $avatarPath = $event->owner->avatar;
                            $avatarUrl = str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                ? $avatarPath : asset('storage/'.$avatarPath);
                        @endphp
                        <dt class="col-sm-4">
                        <img src="{{ $avatarUrl }}" alt="{{ $event->owner->username }}" class="rounded-circle">
                        </dt>
                        <dd class="col-sm-8">{{ $event->owner?->username ?? 'Unknown' }}</dd> 
                    @else
                        <dt class="col-sm-4">Owner</dt>
                        <dd class="col-sm-8 h1 text-bold">
                            <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                            style="width: 40px; height: 40px;" aria-hidden="true">
                            {{ strtoupper(substr($event->owner->name, 0, 1)) }}
                            </span>
                        {{ $event->owner?->username ?? 'Unknown' }}
                        </dd>
                    @endif
                    <h6 class="mb-2">Rating distribution</h6>
                    @for ($rating = 5; $rating >= 1; $rating--)
                        @php
                            $ratingCount = $ratingCounts->get($rating, 0);
                            $ratingPercentage = ($ratingCount / $ratingTotal) * 100;
                        @endphp
                        <div class="d-flex align-items-center gap-2 mb-1 w-25">
                            <span class="small text-nowrap" style="width: 2rem;">👍{{ $rating }}</span>
                            <div class="progress flex-grow-1 ms-3" style="height: 0.75rem;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $ratingPercentage }}%" aria-valuenow="{{ $ratingCount }}" aria-valuemin="0" aria-valuemax="{{ $ratingTotal }}"></div>
                            </div>
                            <span class="small text-muted text-end" style="width: 3rem;">{{ $ratingCount }} users</span>
                        </div>
                    @endfor
                </div>
                @forelse ($event->reviews as $review)
                    <article class="border-top py-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                @if ($review->user)
                                    @php
                                        $avatarPath = $review->user->avatar;
                                        $avatarUrl = $avatarPath
                                            ? (str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                                ? $avatarPath : asset('storage/'.$avatarPath))
                                            : null;
                                    @endphp
                                    <a href="{{ route('profile.show', $review->user->id) }}"
                                        class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" alt="{{ $review->user->username }}" class="rounded-circle"
                                                width="40" height="40" style="object-fit: cover;">
                                        @else
                                            <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;" aria-hidden="true">
                                                {{ strtoupper(substr($review->user->username, 0, 1)) }}
                                            </span>
                                        @endif
                                        <span>
                                            <strong class="text-secondary">{{ $review->user->username }}</strong>
                                        </span>
                                    </a>
                                @else
                                    <span class="text-secondary">Unknown user</span>
                                @endif
                                <div class="text-warning" aria-label="{{ $review->rating }} out of 5 stars">
                                    {{ str_repeat('👍', (int) $review->rating) }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-auto">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i></a>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                                    data-confirm="true" data-confirm-message="Delete this review?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                        aria-label="Delete review {{ $review->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <p class="mb-1 mt-2" style="white-space: pre-wrap;">{{ $review->comment ?: 'No written review.' }}</p>
                        <time class="text-secondary small" datetime="{{ $review->created_at?->toIso8601String() }}">
                            {{ $review->created_at?->format('M j, Y g:i A') ?? '-' }}
                        </time>
                    </article>
                @empty
                    <p class="text-secondary mb-0">No reviews yet.</p>
                @endforelse
                @endif
            </section>
        </div>
    </section>
@endsection
