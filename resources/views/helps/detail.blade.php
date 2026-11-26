@extends('layouts.app')

@section('sidebar', 'helps')
@section('title', __($help->title))

@section('content')
    <style>
        .help-card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .help-cover {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #e9ecef;
            aspect-ratio: 16 / 9;
            margin-bottom: 16px;
        }

        .help-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .help-cover-empty {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888780;
            font-size: 18px;
        }

        .help-status {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #fff;
            color: #3d8fd6;
            border: 1px solid #3d8fd6;
            border-radius: 999px;
            font-size: 12px;
            padding: 2px 10px;
        }

        .help-badge {
            background: #c9e58b;
            border-radius: 999px;
            padding: 2px 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .help-badge-secondary {
            background: #e6f1fb;
            color: #3d8fd6;
            border-radius: 999px;
            padding: 2px 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .help-info-box {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 10px 14px;
            height: 100%;
        }

        .help-info-label {
            color: #888780;
            font-size: 11px;
        }

        .help-info-value {
            font-size: 14px;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .help-help {
            color: #888780;
            font-size: 12px;
        }

        .help-map {
            width: 100%;
            height: 180px;
            border: 0;
            border-radius: 8px;
        }

        .help-avatar {
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

        .help-cover-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            border-radius: 999px;
            font-size: 12px;
            padding: 4px 12px;
            font-weight: 600;
            background: #ffffff;
        }

        .help-detail-badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .help-detail-category {
            background: #c9e58b;
            color: #365314;
        }

        .help-detail-badge.status-today {
            background: #fda4af;
            color: #9f1239;
        }

        .help-detail-badge.status-urgent {
            background: #fde047;
            color: #713f12;
        }

        .help-detail-badge.status-info {
            background: #7dd3fc;
            color: #075985;
        }

        .help-detail-badge.status-closed {
            background: #e5e7eb;
            color: #4b5563;
        }

        .help-detail-badge.status-accepted {
            background: #dcfce7;
            color: #166534;
        }

        .help-detail-badge.status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>

    @php
        $isOwner = auth()->check() && (int) auth()->id() === (int) $help->user_id;

        $helpImageUrl = $help->image_path
            ? asset('storage/' . ltrim($help->image_path, '/'))
            : asset('images/onlylogo.jpg');

        $isDeadlinePassed = $help->application_deadline && $help->application_deadline->isBefore(today());

        $deadlineBadge = $help->application_deadline_badge;

        $myApplication = null;
        $applicationBadge = null;

        $statusLabel = $help->status
            ? __(\Illuminate\Support\Str::headline($help->status))
            : __('Unknown');

        if (auth()->check() && $help->relationLoaded('applications')) {
            $myApplication = $help->applications->first();
        }

        $applicationBadge = match ($myApplication?->status) {
            'pending' => [
                'label' => 'Pending approval',
                'class' => 'status-info',
            ],
            'accepted' => [
                'label' => 'Approved',
                'class' => 'status-accepted',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'class' => 'status-rejected',
            ],
            default => null,
        };

        $canApply =
            auth()->check() && !$isOwner && !$myApplication && $help->status === 'published' && !$isDeadlinePassed;


        $eventDateLabel = null;

        if ($help->event_start_at) {
            $eventDateLabel = \App\Support\LocalizedValue::dateTime($help->event_start_at);

            if ($help->event_end_at) {
                $eventDateLabel .= ' - ' . \App\Support\LocalizedValue::dateTime($help->event_end_at);
            }
        }

        $rewardTypeLabel = match ($help->reward_type) {
            'hourly' => __('Hourly'),
            'fixed' => __('Fixed reward'),
            'negotiable' => __('Negotiable'),
            default => $help->reward_type,
        };

        $category = $help->getRelation('category');

        $rewardLabel = collect([$help->reward_type, $help->reward_amount])
            ->filter(fn($value) => filled($value))
            ->implode(' / ');
    @endphp

    <div class="container py-4" style="max-width: 1000px;">
        {{-- Back --}}
        <a href="{{ route('helps.index') }}" class="text-decoration-none text-dark d-inline-block mb-3">
            &larr; {{ __('Back to helps') }}
        </a>

        @if (session('success'))
            <div class="alert alert-success">
                {{ __(session('success')) }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ __(session('error')) }}
            </div>
        @endif

        <div class="row g-4">
            {{-- Left: Main --}}
            <div class="col-lg-8">
                {{-- Cover image --}}
                <div class="help-cover">
                    @if ($help->image_path)
                        <img src="{{ $helpImageUrl }}" alt="{{ __($help->title) }}" style="{{ $help->imageStyle() }}">
                    @else
                        <div class="help-cover-empty">
                            {{ __('No image') }}
                        </div>
                    @endif

                    @if ($isDeadlinePassed)
                        <span class="help-status text-danger border-danger">
                            {{ __('Application closed') }}
                        </span>
                    @else
                        <span class="help-status">
                            {{ __($statusLabel) }}
                        </span>
                    @endif
                </div>

                {{-- Help basics --}}
                <div class="help-card">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        {{-- @if ($help->category)
                            <span class="help-badge">
                                {{ __($help->category->name) }}
                            </span>
                        @endif

                        @if ($help->requester_type)
                            <span class="help-badge-secondary">
                                {{ __($help->requester_type) }}
                            </span>
                        @endif

                        @if ($help->status)
                            <span class="help-badge-secondary">
                                {{ __($statusLabel) }} --}}
                        @if ($category && filled($category->name))
                            <span class="help-detail-badge help-detail-category">
                                {{ __($category->name) }}
                            </span>
                        @endif

                        <span class="help-detail-badge {{ $deadlineBadge['class'] }}">
                            {{ __($deadlineBadge['label']) }}
                        </span>

                        @if ($applicationBadge)
                            <span class="help-detail-badge {{ $applicationBadge['class'] }}">
                                {{ __($applicationBadge['label']) }}
                            </span>
                        @endif
                    </div>

                    <h1 class="fw-normal mb-2">
                        {{ __($help->title) }}
                    </h1>

                    @if ($help->company_name)
                        <p class="text-muted mb-4">
                            {{ __($help->company_name) }}
                        </p>
                    @endif

                    <div class="row g-2">
                        {{-- Requester type --}}
                        <div class="col-md-4">
                            <div class="help-info-box">
                                <div class="help-info-label">
                                    {{ __('Requester type') }}
                                </div>
                                <div class="help-info-value">
                                    {{ __($help->requester_type) ?: __('Not specified') }}
                                </div>
                            </div>
                        </div>

                        {{-- Event date --}}
                        <div class="col-md-8">
                            <div class="help-info-box">
                                <div class="help-info-label">
                                    {{ __('Event date') }}
                                </div>
                                <div class="help-info-value">
                                    {{ $eventDateLabel ?: __('Not specified') }}
                                </div>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="col-md-6">
                            <div class="help-info-box">
                                <div class="help-info-label">
                                    {{ __('Location') }}
                                </div>
                                <div class="help-info-value">
                                    {{ $help->location ? __($help->location) : __('Not specified') }}
                                </div>
                            </div>
                        </div>

                        {{-- Reward --}}
                        <div class="col-md-6">
                            <div class="help-info-box">
                                <div class="help-info-label">
                                    {{ __('Reward') }}
                                </div>
                                <div class="help-info-value">
                                    {{ __($rewardLabel) ?: __('Not specified') }}
                                </div>
                            </div>
                        </div>

                        {{-- Capacity --}}
                        <div class="col-md-6">
                            <div class="help-info-box">
                                <div class="help-info-label">
                                    {{ __('Capacity') }}
                                </div>
                                <div class="help-info-value">
                                    @if (!is_null($help->capacity))
                                        @if ($help->capacity == 1)
                                            {{ __('Capacity :count person', ['count' => $help->capacity]) }}
                                        @else
                                            {{ __('Capacity :count people', ['count' => $help->capacity]) }}
                                        @endif
                                    @else
                                        {{ __('Not specified') }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Application deadline --}}
                        <div class="col-md-6">
                            <div class="help-info-box">
                                <div class="help-info-label">
                                    {{ __('Application deadline') }}
                                </div>
                                <div class="help-info-value">
                                    @if ($help->application_deadline)
                                        {{ \App\Support\LocalizedValue::dateTime($help->application_deadline, 'date') }}
                                        <span class="text-muted">
                                            ({{ __($deadlineBadge['label']) }})
                                        </span>
                                    @else
                                        {{ __('No deadline') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="help-card">
                    <h5 class="mb-3">
                        {{ __('Help description') }}
                    </h5>

                    <p class="mb-0" style="white-space: pre-line;">{{ __($help->description) }}</p>
                </div>

                {{-- Requirements --}}
                @if ($help->requirements)
                    <div class="help-card">
                        <h5 class="mb-3">
                            {{ __('Requirements') }}
                        </h5>

                        <p class="mb-0" style="white-space: pre-line;">
                            {{ __($help->requirements) }}
                        </p>
                    </div>
                @endif
            </div>



            {{-- Right: Side --}}
            <div class="col-lg-4">
                {{-- Apply --}}
                <div class="help-card">
                    <h5 class="mb-3">
                        {{ __('Apply for this help') }}
                    </h5>

                    @if ($isOwner)
                        <div class="alert alert-info small">
                            {{ __('This is your help posting.') }}
                        </div>

                        <a href="{{ route('helps.applications.index', $help) }}"
                            class="btn btn-outline-primary w-100 mb-2">
                            {{ __('View applications') }}
                        </a>

                        <a href="{{ route('helps.edit', $help) }}" class="btn btn-outline-primary w-100 mb-2">
                            {{ __('Edit Help') }}
                        </a>

                        @if ($errors->has('help'))
                            <div class="alert alert-danger small">
                                {{ $errors->first('help') }}
                            </div>
                        @endif

                        <form action="{{ route('helps.destroy', $help) }}" method="POST"
                            onsubmit="return confirm('{{ __('Are you sure you want to delete this help?') }}')">
                            @csrf
                            @method('DELETE')

                            <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal"
                                data-bs-target="#deletehelpModal{{ $help->id }}">
                                {{ __('Delete Help') }}
                            </button>
                        </form>
                    @elseif ($isDeadlinePassed)
                        <div class="alert alert-secondary text-center small mb-0">
                            {{ __('Applications are closed.') }}
                        </div>
                    @elseif ($myApplication?->status === 'pending')
                        <div class="alert alert-info small mb-0">
                            {{ __('Your application is pending approval.') }}
                        </div>
                    @elseif ($myApplication?->status === 'accepted')
                        <div class="alert alert-success small mb-0">
                            {{ __('Your application has been approved.') }}
                        </div>
                    @elseif ($myApplication?->status === 'rejected')
                        <div class="alert alert-danger small mb-0">
                            {{ __('Your application was rejected.') }}
                        </div>
                    @elseif (!auth()->check())
                        <a href="{{ route('login') }}" class="btn btn-primary w-100">
                            {{ __('Log in to apply') }}
                        </a>
                    @else
                        <form action="{{ route('helps.applications.store', $help) }}" method="POST">
                            @csrf

                            <label for="cover_letter" class="form-label small">
                                {{ __('Message') }}
                                <span class="text-muted">
                                    ({{ __('optional') }})
                                </span>
                            </label>

                            <textarea name="cover_letter" id="cover_letter" rows="5" maxlength="2000"
                                class="form-control @error('cover_letter') is-invalid @enderror"
                                placeholder="{{ __('Tell the company why you are interested.') }}">{{ old('cover_letter') }}</textarea>

                            @error('cover_letter')
                                <div class="text-danger small">
                                    {{ $message }}
                                </div>
                            @enderror

                            <button type="submit" class="btn btn-primary w-100 mt-3">
                                {{ __('Apply now') }}
                            </button>

                            @error('application')
                                <div class="alert alert-danger small">
                                    {{ $message }}
                                </div>
                            @enderror
                        </form>
                    @endif
                </div>

                {{-- Location --}}
                @if ($help->location)
                    <div class="help-card">
                        <h5 class="mb-3">
                            {{ __('Location') }}
                        </h5>

                        <iframe class="help-map" loading="lazy"
                            src="https://maps.google.com/maps?q={{ urlencode($help->location) }}&output=embed"></iframe>

                        <div class="mt-2">
                            {{ __($help->location) }}
                        </div>

                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($help->location) }}"
                            target="_blank" rel="noopener noreferrer" class="small">
                            {{ __('Get Directions') }} &#8599;
                        </a>
                    </div>
                @endif

                {{-- Posted by --}}
                <div class="help-card">
                    <div class="help-help mb-2">
                        {{ __('Posted by') }}
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        @if ($help->user)
                            <a href="{{ route('profile.show', $help->user->id) }}" class="text-decoration-none text-reset">
                                <div class="help-avatar">
                                    {{ strtoupper(substr($help->user->username ?: '?', 0, 1)) }}
                                </div>
                            </a>
                        @else
                            <div class="help-avatar">?</div>
                        @endif

                        <div>
                            <div class="fw-semibold">
                                @if ($help->user)
                                    <a href="{{ route('profile.show', $help->user->id) }}" class="text-decoration-none text-reset">
                                        {{ $help->user->username ?: __('Unknown user') }}
                                    </a>
                                @else
                                    {{ __('Unknown') }}
                                @endif
                            </div>

                            <div class="help-help">
                                {{ __('Help creator') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('helps.partials.delete-help-modal', [
        'help' => $help,
    ])
@endsection
