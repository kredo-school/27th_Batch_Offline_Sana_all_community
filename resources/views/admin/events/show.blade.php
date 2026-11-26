@extends('layouts.app')

@section('title', __('Event Details'))

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <a href="{{ route('admin.events') }}" class="small text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>{{ __('Back to') }} {{ __('Events') }}
                </a>

                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
                    <div>
                        <h1 class="h3 mb-2">{{ $event->title }}</h1>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-{{ $event->status === 'published' ? 'success' : 'secondary' }}">
                                {{ $event->status_label }}
                            </span>
                            <span class="badge bg-{{ $event->visibility === 'public' ? 'primary' : 'dark' }}">
                                {{ $event->visibility_label }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-primary btn-sm"
                        title="{{ __('Edit') }}" aria-label="{{ __('Edit event') }}">
                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    </a>
                </div>

                @if ($event->image)
                    <img class="img-fluid rounded mb-4" src="{{ asset('storage/'.$event->image) }}"
                        alt="{{ $event->title }}" style="max-height: 360px; width: 100%; object-fit: cover;">
                @endif

                <h2 class="h5">{{ __('Description') }}</h2>
                <p class="mb-4" style="white-space: pre-wrap;">{{ $event->description ?: __('No description provided.') }}</p>

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
                        <dd class="col-sm-8">{{ $event->owner?->username ?? __('Unknown') }}</dd>
                    @else
                        <dt class="col-sm-4">{{ __('Owner') }}</dt>
                        <dd class="col-sm-8">{{ $event->owner?->username ?? __('Unknown') }}</dd>
                    @endif
                    

                    <dt class="col-sm-4">{{ __('Categories') }}</dt>
                    <dd class="col-sm-8">
                        @forelse ($event->categories as $category)
                            <span class="badge bg-success me-1">{{ $category->localized_name }}</span>
                        @empty
                            -
                        @endforelse
                    </dd>

                    <dt class="col-sm-4">{{ __('Community') }}</dt>
                    <dd class="col-sm-8">{{ $event->community?->name ?? '-' }}</dd>

                    <dt class="col-sm-4">{{ __('Starts') }}</dt>
                    <dd class="col-sm-8">{{ \App\Support\LocalizedValue::dateTime($event->start_at) }}</dd>

                    <dt class="col-sm-4">{{ __('Join till') }}</dt>
                    <dd class="col-sm-8">{{ \App\Support\LocalizedValue::dateTime($event->join_till) }}</dd>

                    <dt class="col-sm-4">{{ __('Ends') }}</dt>
                    <dd class="col-sm-8">{{ \App\Support\LocalizedValue::dateTime($event->end_at) }}</dd>

                    <dt class="col-sm-4">{{ __('Location') }}</dt>
                    <dd class="col-sm-8">{{ $event->location ?: '-' }}</dd>

                    <dt class="col-sm-4">{{ __('Region') }}</dt>
                    <dd class="col-sm-8">{{ $event->region ?: '-' }}</dd>

                    <dt class="col-sm-4">{{ __('Language') }}</dt>
                    <dd class="col-sm-8">{{ $event->language_label }}</dd>

                    <dt class="col-sm-4">{{ __('Capacity') }}</dt>
                    <dd class="col-sm-8">{{ $event->capacity ?? __('Unlimited') }}</dd>

                    <dt class="col-sm-4">{{ __('Price') }}</dt>
                    <dd class="col-sm-8">{{ number_format((float) $event->price, 2) }}</dd>

                    <dt class="col-sm-4">{{ __('Approval required') }}</dt>
                    <dd class="col-sm-8">{{ $event->approval_required ? __('Yes') : __('No') }}</dd>

                    <dt class="col-sm-4">{{ __('Created') }}</dt>
                    <dd class="col-sm-8">{{ \App\Support\LocalizedValue::dateTime($event->created_at) }}</dd>
                </dl>
            </div>
            <section class="mt-4 ms-5 me-5" aria-labelledby="event-reviews-heading">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <h2 id="event-reviews-heading" class="h5 mb-0">{{ __('Reviews') }}</h2>
                    <span class="text-secondary small">{{ $event->reviews->count() }} {{ __('total') }}</span>
                </div>

                @if ($event->reviews->isNotEmpty())
                <p class="fw-bold mb-2">
                    {{ __('Average rating') }}: {{ number_format($event->reviews->avg('rating'), 1) }} / 5
                </p>
                @php
                    $ratingCounts = $event->reviews->countBy('rating');
                    $ratingTotal = $event->reviews->count();
                @endphp
                <span><strong>{{ __('Event Host') }}</strong></span>
                <div class="mb-3" aria-label="{{ __('Rating distribution') }}">
                    
                    <a href="{{ route('profile.show', $event->owner->id) }}"
                                        class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark">
                     @if ($event->owner->profile?->avatar)
                        @php
                          $avatarPath = $event->owner->profile->avatar;
                            $avatarUrl = str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                ? $avatarPath : asset('storage/'.$avatarPath);
                        @endphp
                         
                        <dt class="col-sm-4">
                        <img src="{{ $avatarUrl }}" alt="{{ $event->owner->username }}" class="rounded-circle navbar-avatar" width="50" height="50">
                        </dt>
                        <dd class="col-sm-8">{{ $event->owner?->username ?? __('Unknown') }}</dd>
                    @else
                        <dt class="col-sm-4">{{ __('Event Host') }}</dt>
                        <dd class="col-sm-8 h1 text-bold">
                            <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px;" aria-hidden="true">
                            {{ strtoupper(substr($event->owner->username ?: '?', 0, 1)) }}
                            </span>
                        {{ $event->owner?->username ?? __('Unknown') }}
                        </dd>
                    @endif
                    </a>
                    <h6 class="mb-2">{{ __('Rating distribution') }}</h6>
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
                            <span class="small text-muted text-end" style="width: 3rem;">{{ $ratingCount }} {{ __('users') }}</span>
                        </div>
                    @endfor
                </div>
                @forelse ($event->reviews as $review)
                    <article class="border-top py-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                @if ($review->user)
                                    @php
                                        $avatarPath = $review->user->profile?->avatar;
                                        $avatarUrl = $avatarPath
                                            ? (str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                                ? $avatarPath : asset('storage/'.$avatarPath))
                                            : null;
                                    @endphp
                                    <a href="{{ route('profile.show', $review->user->id) }}"
                                        class="d-inline-flex align-items-center gap-2 text-decoration-none text-dark">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" alt="{{ $review->user->username }}" class="rounded-circle"
                                                width="30" height="30" style="object-fit: cover;">
                                        @else
                                            <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                                style="width: 30px; height: 30px;" aria-hidden="true">
                                                {{ strtoupper(substr($review->user->username, 0, 1)) }}
                                            </span>
                                        @endif
                                        <span>
                                            <strong class="text-secondary">{{ $review->user->username }}</strong>
                                        </span>
                                    </a>
                                @else
                                    <span class="text-secondary">{{ __('Unknown user') }}</span>
                                @endif
                                <div class="text-warning" aria-label="{{ $review->rating }} out of 5 stars">
                                    {{ str_repeat('👍', (int) $review->rating) }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 ms-auto">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-outline-secondary btn-sm"
                                    title="{{ __('View') }}" aria-label="{{ __('View') }}">
                                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                        <p class="mb-1 mt-2" style="white-space: pre-wrap;">{{ $review->comment ?: __('No written review.') }}</p>
                        <time class="text-secondary small" datetime="{{ $review->created_at?->toIso8601String() }}">
                            {{ \App\Support\LocalizedValue::dateTime($review->created_at) }}
                        </time>
                    </article>
                @empty
                    <p class="text-secondary mb-0">{{ __('No reviews yet.') }}</p>
                @endforelse
                @endif
            </section>
        </div>
    </section>
@endsection
