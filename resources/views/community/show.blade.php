@extends('layouts.app')

@section('title', $community->name ?? 'Community Detail')

@section('content')
<div class="container py-4">
    <!-- 成功メッセージ表示 -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- コミュニティ・メインカード HEADER -->
    <div class="card bg-white border shadow-sm rounded-3 overflow-hidden mb-4">
        <!-- Cover Banner (背景カバー画像エリア：写真があれば写真の縦横比に合わせて枠の高さが変わる) -->
        <div class="position-relative d-flex align-items-center justify-content-center overflow-hidden {{ $community->image ? 'bg-white' : 'bg-light community-cover-empty' }}">
            @if ($community->image)
                <img src="{{ asset('storage/' . $community->image) }}" alt="{{ $community->name }} cover image" class="d-block w-100 object-fit-contain" style="max-height: 480px;">
            @else
                <h1 class="display-3 fw-bold text-secondary opacity-50 mb-0">
                    {{ strtoupper(substr($community->name, 0, 2)) }}
                </h1>
            @endif
        </div>

        <!-- Community Info Bar (アイコン、名前、ボタン) -->
        <div class="card-body p-4 position-relative">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3" style="margin-top: -60px;">
                
                <!-- アイコン & コミュニティ名 -->
                <div class="d-flex align-items-end gap-3">
                    <div class="bg-white border rounded-3 p-1 shadow-sm d-flex align-items-center justify-content-center" style="width: 88px; height: 88px; min-width: 88px;">
                        <x-icon :community="$community" class="w-100 h-100 rounded-2 fs-3" />
                    </div>
                    <div class="mb-1">
                        <span class="badge bg-light text-secondary border mb-1">{{ $community->category }}</span>
                        <h2 class="fw-bold text-dark mb-1 h3">{{ $community->name }}</h2>
                        <p class="text-muted small mb-0">{{ $community->description }}</p>
                    </div>
                </div>

                <!-- アクションボタン群 -->
                <div class="d-flex align-items-center gap-2">
                    @if (auth()->id() === $community->owner_id)
                        <!-- 編集ボタン (Edit community) -->
                        <a href="{{ route('community.edit', $community->id) }}" class="btn btn-outline-secondary btn-sm fw-bold px-3">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit community
                        </a>
                        <a href="#post-event" class="btn btn-primary btn-sm text-white fw-bold px-3">+ Post an event</a>
                    @endif
                </div>
            </div>

            <!-- タブナビゲーション (Home, Events, Chat, Members, About) -->
            <ul class="nav nav-tabs border-bottom-0 mt-4" id="communityTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active text-muted border-0 px-3 pb-2" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab">Home</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab">Events <span class="badge bg-light text-dark border rounded-pill">{{ $eventCount }}</span></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab">Chat</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">Members <span class="badge bg-light text-dark border rounded-pill">{{ $memberCount }}</span></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">About</button>
                </li>
            </ul>
        </div>
    </div>

    <style>
        /* カバー写真がないときの枠の高さ */
        .community-cover-empty {
            height: 180px;
        }

        #communityTab .nav-link.active {
            color: var(--bs-dark) !important;
            font-weight: 700;
            background: transparent;
            border-bottom: 2px solid var(--bs-primary) !important;
        }
    </style>

    <div class="tab-content" id="communityTabContent">
    <!-- Home タブ: コンテンツレイアウト (左: イベント一覧 / 右: サイドバー) -->
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
    <div class="row g-4">
        <!-- 左メインエリア -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark h5 mb-0">Upcoming events</h4>
            </div>
            @if (auth()->id() === $community->owner_id)
                <form id="post-event" action="{{ route('community.events.store', $community->id) }}" method="POST" class="card bg-white border shadow-sm rounded-3 p-3 mb-4">
                    @csrf
                    <h5 class="fw-bold text-dark h6">Post an event</h5>
                    <div class="mb-3">
                        <label for="event-title" class="form-label">Event title</label>
                        <input id="event-title" name="title" class="form-control" value="{{ old('title') }}" maxlength="200" required>
                    </div>
                    <div class="mb-3">
                        <label for="event-description" class="form-label">Description</label>
                        <textarea id="event-description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="event-start" class="form-label">Starts</label>
                            <input id="event-start" type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="event-end" class="form-label">Ends</label>
                            <input id="event-end" type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="event-location" class="form-label">Location</label>
                            <input id="event-location" name="location" class="form-control" value="{{ old('location') }}" maxlength="255">
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Post event</button>
                    </div>
                </form>
            @endif
            <div class="card bg-white border shadow-sm rounded-3 p-3 mb-4">
                @forelse ($community->events as $event)
                    <x-event-row :event="$event" class="py-3 border-bottom" />
                @empty
                    <p class="text-muted small mb-0">There are no upcoming events for this community yet.</p>
                @endforelse
            </div>
        </div>

        <!-- 右サイドバー (About this community & Organizers) -->
        <div class="col-lg-4">
            <!-- About Card -->
            <div class="card bg-white border shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold text-dark h6 mb-3">About this community</h5>
                <p class="text-muted small mb-4">{{ $community->description }}</p>

                <div class="d-flex flex-column gap-2 small border-top pt-3">
                    <div class="d-flex justify-content-between text-muted">
                        <span><i class="fa-regular fa-calendar me-2"></i> Events</span>
                        <strong class="text-dark">{{ $eventCount }}</strong>
                    </div>
                    @if ($community->location)
                        <div class="d-flex justify-content-between text-muted">
                            <span><i class="fa-solid fa-location-dot me-2"></i> Area</span>
                            <strong class="text-dark">{{ $community->location }}</strong>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between text-muted">
                        <span><i class="fa-solid fa-lock me-2"></i> Access</span>
                        <strong class="text-dark">{{ ['public' => 'Public', 'approval' => 'Approval required', 'private' => 'Private'][$community->visibility] ?? 'Public' }}</strong>
                    </div>
                </div>
                @if ($community->guidelines)
                    <div class="border-top mt-3 pt-3">
                        <h6 class="fw-bold text-dark">Welcome & guidelines</h6>
                        <p class="text-muted small mb-0">{!! nl2br(e($community->guidelines)) !!}</p>
                    </div>
                @endif
            </div>

            <!-- Organizers Card -->
            <div class="card bg-white border shadow-sm rounded-3 p-4">
                <h5 class="fw-bold text-dark h6 mb-3">Organizers</h5>
                @if ($community->owner)
                    <div class="d-flex align-items-center gap-3">
                        <x-avatar :user="$community->owner" />
                        <div>
                            <strong class="d-block text-dark small">{{ $community->owner->name }}</strong>
                            <small class="text-muted" style="font-size: 0.75rem;">Community organizer</small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    </div>

    <!-- Events タブ: 今後のイベント一覧 -->
    <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
        <div class="card bg-white border shadow-sm rounded-3 p-3">
            @forelse ($allEvents as $event)
                <x-event-row :event="$event" show-description class="py-3 border-bottom" />
            @empty
                <p class="text-muted small mb-0">There are no upcoming events for this community yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Chat タブ: 未実装 -->
    <div class="tab-pane fade" id="chat" role="tabpanel" aria-labelledby="chat-tab">
        <div class="card bg-white border shadow-sm rounded-3 p-4 text-center">
            <p class="text-muted small mb-0">Community chat is coming soon.</p>
        </div>
    </div>

    <!-- Members タブ: オーナー + 承認済みメンバー -->
    <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
        <div class="card bg-white border shadow-sm rounded-3 p-3">
            @foreach (collect([$community->owner])->filter()->merge($community->members->where('id', '!=', $community->owner_id)) as $member)
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <x-avatar :user="$member" />
                    <div>
                        <strong class="d-block text-dark small">{{ $member->name }}</strong>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            {{ $member->id === $community->owner_id ? 'Community organizer' : ucfirst($member->pivot->role ?? 'member') }}
                        </small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- About タブ -->
    <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about-tab">
        <div class="card bg-white border shadow-sm rounded-3 p-4">
            <h5 class="fw-bold text-dark h6 mb-3">About this community</h5>
            <p class="text-muted small">{!! nl2br(e($community->description)) !!}</p>
            @if ($community->guidelines)
                <h6 class="fw-bold text-dark mt-4">Welcome & guidelines</h6>
                <p class="text-muted small mb-0">{!! nl2br(e($community->guidelines)) !!}</p>
            @endif
        </div>
    </div>
    </div>
</div>
@endsection