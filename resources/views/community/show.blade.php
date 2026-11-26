@extends('layouts.app')

@section('sidebar', 'community')

@section('title', $community->name ?? 'Community Detail')

@section('content')
<div class="container py-4">
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
        <!-- Cover Banner (背景カバー画像エリア：プロフィールと同じく高さ 260px で固定し、写真は切らずに全体を出す) -->
        <div class="position-relative d-flex align-items-center justify-content-center overflow-hidden community-cover {{ $community->image ? 'bg-white' : 'bg-light' }}">
            @if ($community->image)
                <img src="{{ asset('storage/' . $community->image) }}" alt="{{ $community->name }} cover image" class="d-block w-100 h-100 object-fit-contain">
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
                        @if ($community->category)
                            <span class="badge bg-light text-secondary border mb-1">{{ $community->localized_category }}</span>
                        @endif
                        <h2 class="fw-bold text-dark mb-1 h3">{{ $community->name }}</h2>
                        <p class="text-muted small mb-0">{{ $community->description }}</p>
                    </div>
                </div>

                <!-- アクションボタン群 -->
                <div class="d-flex align-items-center gap-2">
                    @if (auth()->id() === $community->owner_id)
                        <!-- 編集ボタン (Edit community) -->
                        <a href="{{ route('community.edit', $community->id) }}" class="btn btn-outline-secondary btn-sm fw-bold px-3">
                            <i class="fa-solid fa-pen-to-square me-1"></i> {{ __('Edit community') }}
                        </a>
                        <a href="{{ route('community.events.create', $community->id) }}" class="btn btn-primary btn-sm text-white fw-bold px-3">+ {{ __('Post an event') }}</a>
                    @elseif ($membershipStatus === 'approved')
                        <span class="badge rounded-pill community-joined-badge px-3 py-2"><i class="fa-solid fa-check me-1"></i> {{ __('Joined') }}</span>
                        <form action="{{ route('community.leave', $community->id) }}" method="POST"
                            data-confirm="true" data-confirm-message="Leave this community?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold px-3">{{ __('Leave') }}</button>
                        </form>
                    @elseif ($membershipStatus === 'invited')
                        {{-- オーナーから招待されている人: 承諾すると参加、断ると招待が消える --}}
                        <span class="badge rounded-pill community-joined-badge px-3 py-2"><i class="fa-solid fa-envelope-open-text me-1"></i> You're invited</span>
                        <form action="{{ route('community.invitations.accept', $community->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary btn-sm text-white fw-bold px-3">Accept invitation</button>
                        </form>
                        <form action="{{ route('community.leave', $community->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold px-3">Decline</button>
                        </form>
                    @elseif ($membershipStatus === 'pending')
                        <span class="badge rounded-pill bg-light text-secondary border px-3 py-2">{{ __('Request pending') }}</span>
                        <form action="{{ route('community.leave', $community->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold px-3">{{ __('Cancel request') }}</button>
                        </form>
                    @elseif ($community->visibility === 'private')
                        <span class="badge rounded-pill bg-light text-secondary border px-3 py-2"><i class="fa-solid fa-lock me-1"></i> Invite only</span>
                    @else
                        <form action="{{ route('community.join', $community->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm text-white fw-bold px-3">
                                {{ $community->visibility === 'approval' ? __('Request to join') : '+ ' . __('Join community') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- タブナビゲーション (Home, Events, Chat, Members, About) -->
            <ul class="nav nav-tabs border-bottom-0 mt-4" id="communityTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active text-muted border-0 px-3 pb-2" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab">{{ __('Community') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab">{{ __('Events') }} <span class="badge bg-light text-dark border rounded-pill">{{ $eventCount }}</span></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab">{{ __('Chat') }}
                        @if ($canChat)
                            <span class="badge rounded-pill community-chat-unread" @if ($unreadCount === 0) hidden @endif><span class="community-chat-unread-count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span><span class="visually-hidden"> unread messages</span></span>
                            {{-- 誰からの未読か（最近送った人の名前と、他の人数） --}}
                            <span class="community-chat-unread-from" title="{{ implode(', ', $unreadSenders) }}" @if (empty($unreadSenders)) hidden @endif><span class="community-chat-unread-name">{{ $unreadSenders[0] ?? '' }}</span><span class="community-chat-unread-more">{{ count($unreadSenders) > 1 ? ' +' . (count($unreadSenders) - 1) : '' }}</span></span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="members-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">{{ __('Members') }} <span class="badge bg-light text-dark border rounded-pill">{{ $memberCount }}</span></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-muted border-0 px-3 pb-2" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">{{ __('About') }}</button>
                </li>
            </ul>
        </div>
    </div>

    <style>
        /* 背景写真の枠の高さ（写真の有る無しに関係なく、プロフィールの背景写真と同じ 260px） */
        .community-cover {
            height: 260px;
        }

        /* イベントのカードの並び（プロフィールのカードの並びと同じ間隔） */
        .community-event-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .community-event-grid--narrow {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .community-event-empty {
            grid-column: 1 / -1;
            margin: 0;
            padding: 32px 24px;
            border: 1px dashed #dfe8ed;
            border-radius: 12px;
            background: #fff;
            color: #647580;
            font-size: 0.9rem;
            text-align: center;
        }

        @media (max-width: 1200px) {
            .community-event-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .community-event-grid,
            .community-event-grid--narrow {
                grid-template-columns: 1fr;
            }
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
                <h4 class="fw-bold text-dark h5 mb-0">{{ __('Upcoming Events') }}</h4>
            </div>
            {{-- プロフィールと同じ画像付きのカードで並べる（横幅が狭いので 2 列） --}}
            <div class="community-event-grid community-event-grid--narrow mb-4">
                @forelse ($community->events as $event)
                    @include('events.partials.post', ['event' => $event, 'showCommunity' => false])
                @empty
                    <p class="community-event-empty">{{ __('There are no upcoming events for this community yet.')}}</p>
                @endforelse
            </div>
        </div>

        <!-- 右サイドバー (About this community & Organizers) -->
        <div class="col-lg-4">
            <!-- About Card -->
            <div class="card bg-white border shadow-sm rounded-3 p-4 mb-4">
                <h5 class="fw-bold text-dark h6 mb-3">{{ __('About this community') }}</h5>
                <p class="text-muted small mb-4">{{ $community->description }}</p>

                <div class="d-flex flex-column gap-2 small border-top pt-3">
                    <div class="d-flex justify-content-between text-muted">
                        <span><i class="fa-regular fa-calendar me-2"></i> {{ __('Events') }}</span>
                        <strong class="text-dark">{{ $eventCount }}</strong>
                    </div>
                    @if ($community->location)
                        <div class="d-flex justify-content-between text-muted">
                            <span><i class="fa-solid fa-location-dot me-2"></i> {{ __('Area') }}</span>
                            <strong class="text-dark">{{ $community->location }}</strong>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between text-muted">
                        <span><i class="fa-solid fa-lock me-2"></i> {{ __('Access') }}</span>
                        <strong class="text-dark">{{ $community->visibility_label }}</strong>
                    </div>
                </div>
                @if ($community->guidelines)
                    <div class="border-top mt-3 pt-3">
                        <h6 class="fw-bold text-dark">{{ __('Welcome & guideline') }}</h6>
                        <p class="text-muted small mb-0">{!! nl2br(e($community->guidelines)) !!}</p>
                    </div>
                @endif
            </div>

            <!-- Organizers Card -->
            <div class="card bg-white border shadow-sm rounded-3 p-4">
                <h5 class="fw-bold text-dark h6 mb-3">{{ __('Organizers') }}</h5>
                @if ($community->owner)
                    <a href="{{ route('profile.show', $community->owner->id) }}" class="d-flex align-items-center gap-3 community-profile-link">
                        <x-avatar :user="$community->owner" />
                        <div>
                            <strong class="d-block text-dark small">{{ $community->owner->username ?: __('Unknown user') }}</strong>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ __('Community Organizer') }}</small>
                        </div>
                    </a>
                @endif
                {{-- 副管理者もオーガナイザーとして並べる --}}
                @foreach ($community->members->filter(fn ($member) => $member->id !== $community->owner_id && $member->pivot->role === 'moderator') as $moderator)
                    <a href="{{ route('profile.show', $moderator->id) }}" class="d-flex align-items-center gap-3 community-profile-link mt-3">
                        <x-avatar :user="$moderator" />
                        <div>
                            <strong class="d-block text-dark small">{{ $moderator->username ?: __('Unknown user') }}</strong>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ __('Moderator')}}</small>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    </div>

    <!-- Events タブ: 今後のイベント一覧 -->
    <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
        {{-- プロフィールと同じ画像付きのカードで並べる --}}
        <div class="community-event-grid">
            @forelse ($allEvents as $event)
                @include('events.partials.post', ['event' => $event, 'showCommunity' => false])
            @empty
                <p class="text-muted small mb-0">{{ __('There are no upcoming events for this community yet.')}}</p>
            @endforelse
        </div>

        {{-- 終了・中止したイベント（タブの横の数字に含まれているイベントは、どこかに必ず出す） --}}
        @if ($pastEvents->isNotEmpty())
            <h6 class="fw-bold text-dark mt-4 mb-3">Past &amp; cancelled events</h6>
            <div class="community-event-grid">
                @foreach ($pastEvents as $event)
                    {{-- プロフィールと同じイベントの掲載（画像の左上に Cancelled / Ended の印が付く） --}}
                    @include('events.partials.post', ['event' => $event, 'showCommunity' => false])
                @endforeach
            </div>
        @endif
    </div>

    <!-- Chat タブ: オーナーと承認済みメンバーだけが使える -->
    <div class="tab-pane fade" id="chat" role="tabpanel" aria-labelledby="chat-tab">
        @if ($canChat)
            <div class="card bg-white border shadow-sm rounded-3 overflow-hidden community-chat"
                data-chat-url="{{ route('community.chat.index', $community->id) }}"
                data-unread-url="{{ route('community.chat.unread', $community->id) }}"
                data-read-url="{{ route('community.chat.read', $community->id) }}"
                data-community-id="{{ $community->id }}">
                <div class="community-chat-body">
                    <ol class="community-chat-list" aria-live="polite"></ol>
                    <div class="community-chat-empty">{{ __('Loading messages...')}}</div>
                </div>
                <div class="community-chat-status" role="status" aria-live="polite"></div>
                {{-- 送る前に選んだ画像を確認できる（× で取り消し） --}}
                <div class="community-chat-attachment" hidden>
                    <img alt="Selected photo">
                    <span class="community-chat-attachment-name"></span>
                    <button type="button" class="community-chat-attachment-remove" aria-label="Remove photo" title="Remove photo">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <form action="{{ route('community.chat.store', $community->id) }}" method="POST" enctype="multipart/form-data" class="community-chat-form">
                    @csrf
                    <label class="btn community-chat-attach" title="Attach a photo">
                        <i class="fa-regular fa-image" aria-hidden="true"></i>
                        <span class="visually-hidden">{{ __('Attach a photo')}}</span>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" hidden>
                    </label>
                    <textarea name="message" class="form-control" rows="1" maxlength="2000"
                        placeholder="{{ __('Write a message...') }}" aria-label="Write a message"></textarea>
                    <button type="submit" class="btn community-chat-send" aria-label="Send message">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
                <div class="community-chat-hint">{{ __('Enter to send')}} &middot; {{ __('Shift + Enter for a new line')}}</div>
            </div>
        @else
            <div class="card bg-white border shadow-sm rounded-3 p-4 text-center">
                <p class="text-muted small mb-0">{{ __('Only members of this community can join the chat.')}}</p>
            </div>
        @endif
    </div>

    <!-- Members タブ: オーナー + 承認済みメンバー -->
    <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
        {{-- 招待（オーナーだけに表示）: ユーザー名で招待し、承諾待ちの人を取り消せる --}}
        @if (auth()->id() === $community->owner_id)
            <div class="card bg-white border shadow-sm rounded-3 p-3 mb-3 community-invite">
                <h6 class="fw-bold text-dark mb-1">{{ __('Invite members') }}</h6>
                <p class="text-muted small mb-2">
                    {{ $community->visibility === 'private'
                        ? 'This community is invite only. Only people you invite can join.'
                        : __('Invited people can join right away without sending a request.') }}
                </p>
                <form action="{{ route('community.invitations.store', $community->id) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <div class="flex-grow-1">
                        <label for="invite-username" class="visually-hidden">{{ __('Username')}}</label>
                        <input type="text" id="invite-username" name="username" value="{{ old('username') }}" maxlength="255" required
                            placeholder="{{ __('Username') }}" autocomplete="off"
                            class="form-control form-control-sm @error('username', 'invite') is-invalid @enderror">
                        @error('username', 'invite')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm text-white fw-bold px-3 align-self-start">{{ __('Invite') }}</button>
                </form>

                @if ($invitedMembers->isNotEmpty())
                    <h6 class="fw-bold text-dark small mt-3 mb-1">Waiting for a reply <span class="badge rounded-pill community-joined-badge">{{ $invitedMembers->count() }}</span></h6>
                    @foreach ($invitedMembers as $invitee)
                        <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                            <x-avatar :user="$invitee" />
                            <strong class="text-dark small flex-grow-1">{{ $invitee->username ?: __('Unknown user') }}</strong>
                            <form action="{{ route('community.members.remove', [$community->id, $invitee->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-secondary btn-sm">Cancel invite</button>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

        {{-- 参加申請（オーナーだけに表示） --}}
        @if ($pendingMembers->isNotEmpty())
            <div class="card bg-white border shadow-sm rounded-3 p-3 mb-3 community-requests">
                <h6 class="fw-bold text-dark mb-2">{{ __('Join requests') }} <span class="badge rounded-pill community-joined-badge">{{ $pendingMembers->count() }}</span></h6>
                @foreach ($pendingMembers as $requester)
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                        <x-avatar :user="$requester" />
                        <strong class="text-dark small flex-grow-1">{{ $requester->username ?: __('Unknown user') }}</strong>
                        <form action="{{ route('community.members.approve', [$community->id, $requester->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary btn-sm text-white">{{ __('Approve') }}</button>
                        </form>
                        <form action="{{ route('community.members.remove', [$community->id, $requester->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm">{{ __('Decline') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card bg-white border shadow-sm rounded-3 p-3 community-member-list">
            @foreach (collect([$community->owner])->filter()->merge($community->members->where('id', '!=', $community->owner_id)) as $member)
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <a href="{{ route('profile.show', $member->id) }}" class="d-flex align-items-center gap-3 flex-grow-1 community-profile-link">
                        <x-avatar :user="$member" />
                        <div>
                            <strong class="d-block text-dark small">{{ $member->username ?: __('Unknown user') }}</strong>
                            @if ($member->id === $community->owner_id)
                                <small class="text-muted" style="font-size: 0.75rem;">{{ __('Community Organizer') }}</small>
                            @elseif (($member->pivot->role ?? 'member') === 'moderator')
                                <span class="badge rounded-pill community-moderator-badge"><i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>{{ __('Moderator') }}</span>
                            @else
                                <small class="text-muted" style="font-size: 0.75rem;">{{ __('Member') }}</small>
                            @endif
                        </div>
                    </a>
                    {{-- オーナーだけ: 副管理者にする・外す、オーナーを渡す、メンバーから外す --}}
                    @if (auth()->id() === $community->owner_id && $member->id !== $community->owner_id)
                        @php($isModerator = ($member->pivot->role ?? 'member') === 'moderator')
                        <div class="dropdown">
                            {{-- 気づきやすいように、文字付きの枠のボタンにする --}}
                            <button class="btn btn-sm community-member-menu dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Manage') }} {{ $member->username ?: __('Unknown user') }}">
                                <i class="fa-solid fa-user-gear me-1" aria-hidden="true"></i>{{ __('Manage') }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <form action="{{ route('community.members.role', [$community->id, $member->id]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="role" value="{{ $isModerator ? 'member' : 'moderator' }}">
                                        <button type="submit" class="dropdown-item small">
                                            <i class="fa-solid fa-shield-halved me-2 text-muted" aria-hidden="true"></i>{{ $isModerator ? __('Remove moderator') : __('Make moderator') }}
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="{{ route('community.owner.transfer', $community->id) }}" method="POST"
                                        data-confirm="true" data-confirm-message="{{ __('Make')}} {{ $member->username ?: __('Unknown user') }} {{ __('the owner of this community? You will stay as a regular member and can no longer edit the community.')}}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="user_id" value="{{ $member->id }}">
                                        <button type="submit" class="dropdown-item small">
                                            <i class="fa-solid fa-crown me-2 text-muted" aria-hidden="true"></i>{{ __('Make owner') }}
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('community.members.remove', [$community->id, $member->id]) }}" method="POST"
                                        data-confirm="true" data-confirm-message="{{ __('Remove')}} {{ $member->username ?: __('Unknown user') }} {{ __('from this community?')}}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item small text-danger">
                                            <i class="fa-solid fa-user-minus me-2" aria-hidden="true"></i>{{ __('Remove from community') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- About タブ -->
    <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about-tab">
        <div class="card bg-white border shadow-sm rounded-3 p-4">
            <h5 class="fw-bold text-dark h6 mb-3">{{ __('About this community') }}</h5>
            <p class="text-muted small">{!! nl2br(e($community->description)) !!}</p>
            @if ($community->guidelines)
                <h6 class="fw-bold text-dark mt-4">{{ __('Welcome & guideline') }}</h6>
                <p class="text-muted small mb-0">{!! nl2br(e($community->guidelines)) !!}</p>
            @endif
        </div>
    </div>
    </div>
</div>

<style>
    .community-chat {
        scroll-margin: 90px 0 16px;
    }

    /* Chat タブの未読バッジ */
    .community-chat-unread {
        background: #dc3545;
        color: #fff;
    }

    /* プロフィールへのリンク */
    .community-profile-link {
        text-decoration: none;
    }

    .community-profile-link:hover strong,
    .community-chat-name a:hover {
        text-decoration: underline;
    }

    .community-chat-name a {
        color: inherit;
        text-decoration: none;
    }

    .community-chat-unread[hidden],
    .community-chat-unread-from[hidden] {
        display: none;
    }

    .community-chat-unread-from {
        display: inline-flex;
        max-width: 150px;
        margin-left: 2px;
        vertical-align: middle;
        color: #495057;
        font-size: 0.72rem;
        white-space: pre;
    }

    .community-chat-unread-name {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .community-chat-body {
        position: relative;
        background: #f8f9fa;
    }

    .community-chat-list {
        display: flex;
        flex-direction: column;
        height: clamp(360px, 60vh, 560px);
        overflow-y: auto;
        margin: 0;
        padding: 16px 20px;
        list-style: none;
    }

    /* メッセージがないとき・読み込み中の案内（リストの中央に出す） */
    .community-chat-empty {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        color: #6c757d;
        font-size: 0.9rem;
        pointer-events: none;
    }

    .community-chat-empty[hidden] {
        display: none;
    }

    /* 日付の区切り */
    .community-chat-day {
        align-self: center;
        margin: 16px 0 6px;
        padding: 2px 12px;
        border-radius: 999px;
        background: #e9ecef;
        color: #495057;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .community-chat-day:first-child {
        margin-top: 0;
    }

    .community-chat-item {
        display: flex;
        gap: 10px;
        max-width: 72%;
        margin-top: 14px;
        align-self: flex-start;
    }

    /* 同じ人の続けての発言は詰めて表示する */
    .community-chat-item--grouped {
        margin-top: 3px;
    }

    .community-chat-item--mine {
        flex-direction: row-reverse;
        align-self: flex-end;
    }

    .community-chat-item--mine .community-chat-name {
        text-align: right;
    }

    .community-chat-avatar {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        overflow: hidden;
        border-radius: 50%;
        background: #F58634;
        color: #fff;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .community-chat-item--grouped .community-chat-avatar {
        height: 0;
        visibility: hidden;
    }

    .community-chat-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .community-chat-content {
        min-width: 0;
    }

    .community-chat-name {
        margin-bottom: 3px;
        color: #212529;
        font-size: 0.8rem;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .community-chat-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }

    .community-chat-item--mine .community-chat-row {
        flex-direction: row-reverse;
    }

    .community-chat-bubble {
        min-width: 0;
        padding: 9px 14px;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        background: #fff;
        color: #212529;
        font-size: 0.92rem;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .community-chat-item--mine .community-chat-bubble {
        border-color: #fbd3b4;
        background: #fdebdc;
    }

    .community-chat-bubble p {
        margin: 0;
        white-space: pre-wrap;
    }

    .community-chat-translation {
        margin-top: 6px !important;
        padding-top: 6px;
        border-top: 1px solid #dce4e0;
        color: #174e45;
        font-size: 0.9em;
    }

    /* 発言の横の時刻と削除ボタン */
    .community-chat-side {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        gap: 6px;
        padding-bottom: 2px;
        color: #6c757d;
        font-size: 0.7rem;
        white-space: nowrap;
    }

    .community-chat-item--mine .community-chat-side {
        flex-direction: row-reverse;
    }

    /* 編集・削除ボタン */
    .community-chat-edit-button,
    .community-chat-delete {
        padding: 0 2px;
        border: 0;
        background: transparent;
        color: #6c757d;
        font-size: 0.78rem;
    }

    .community-chat-edit-button:hover {
        color: #F58634;
    }

    .community-chat-delete:hover {
        color: #dc3545;
    }

    /* 発言の編集中はその場に入力欄を出す */
    .community-chat-item--editing {
        width: 100%;
    }

    .community-chat-item--editing .community-chat-content,
    .community-chat-edit {
        flex: 1;
        min-width: 0;
    }

    .community-chat-edit textarea {
        max-height: 160px;
        border-radius: 12px;
        font-size: 0.92rem;
        resize: none;
    }

    .community-chat-edit textarea:focus {
        border-color: #F58634;
        box-shadow: 0 0 0 0.25rem rgba(245, 134, 52, 0.25);
    }

    .community-chat-edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 6px;
        margin-top: 6px;
    }

    .community-chat-edit-save,
    .community-chat-edit-save:hover,
    .community-chat-edit-save:focus-visible,
    .community-chat-edit-save:disabled {
        background: #F58634;
        color: #fff;
    }

    .community-chat-edit-save:hover {
        background: #e0741f;
    }

    .community-chat-status {
        padding: 8px 16px 0;
        color: #dc3545;
        font-size: 0.82rem;
    }

    .community-chat-status:empty {
        display: none;
    }

    .community-chat-form {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding: 12px 16px 4px;
        border-top: 1px solid #e9ecef;
    }

    .community-chat-status:not(:empty) + .community-chat-form {
        border-top: 0;
    }

    .community-chat-form textarea {
        min-height: 40px;
        max-height: 120px;
        padding: 8px 16px;
        border-radius: 20px;
        resize: none;
    }

    .community-chat-form textarea:focus {
        border-color: #F58634;
        box-shadow: 0 0 0 0.25rem rgba(245, 134, 52, 0.25);
    }

    .community-chat-send {
        display: grid;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        place-items: center;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: #F58634;
        color: #fff;
    }

    .community-chat-send:hover,
    .community-chat-send:focus-visible,
    .community-chat-send:disabled {
        background: #e0741f;
        color: #fff;
    }

    /* 画像を添付するボタン */
    .community-chat-attach {
        display: grid;
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        place-items: center;
        padding: 0;
        border: 1px solid #dee2e6;
        border-radius: 50%;
        color: #6c757d;
        font-size: 1.05rem;
    }

    .community-chat-attach:hover,
    .community-chat-attach:focus-within {
        border-color: #F58634;
        color: #F58634;
    }

    /* 送る前の画像の確認 */
    .community-chat-attachment {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 10px 16px 0;
        padding: 8px 10px;
        border: 1px solid #fbd3b4;
        border-radius: 12px;
        background: #fff8f2;
        font-size: 0.82rem;
    }

    .community-chat-attachment[hidden] {
        display: none;
    }

    .community-chat-attachment img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
    }

    .community-chat-attachment-name {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        color: #495057;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .community-chat-attachment-remove {
        border: 0;
        background: transparent;
        color: #6c757d;
    }

    .community-chat-attachment-remove:hover {
        color: #dc3545;
    }

    .community-chat-attachment:not([hidden]) + .community-chat-form {
        border-top: 0;
    }

    /* チャットの中の画像（押すと元の大きさで開く） */
    .community-chat-image {
        display: block;
        max-width: min(280px, 100%);
    }

    .community-chat-image img {
        display: block;
        max-width: 100%;
        max-height: 260px;
        border-radius: 10px;
        object-fit: cover;
    }

    .community-chat-image + p {
        margin-top: 6px !important;
    }

    .community-chat-hint {
        padding: 0 16px 10px 32px;
        color: #6c757d;
        font-size: 0.7rem;
    }

    @media (hover: none) {
        .community-chat-hint {
            padding-bottom: 6px;
            visibility: hidden;
        }
    }

    @media (max-width: 640px) {
        .community-chat-list {
            padding: 12px;
        }

        .community-chat-item {
            max-width: 90%;
        }
    }

    /* 参加中バッジと参加申請の数 */
    .community-joined-badge {
        background: #fdebdc;
        color: #F58634;
    }

    /* 副管理者（モデレーター）の印 */
    .community-moderator-badge {
        background: #e7f1ff;
        color: #0a58ca;
        font-size: 0.7rem;
        font-weight: 600;
    }

    /* メンバーの管理ボタン（オーナーだけに表示）: オレンジの枠で目立たせる */
    /* Bootstrap のボタンの変数で色を決める（押したとき・メニューを開いたときも同じ色にそろう） */
    .community-member-menu {
        --bs-btn-padding-x: 12px;
        --bs-btn-padding-y: 4px;
        --bs-btn-font-size: 0.8rem;
        --bs-btn-font-weight: 600;
        --bs-btn-border-radius: 999px;
        --bs-btn-color: #d96a1c;
        --bs-btn-bg: #fff;
        --bs-btn-border-color: #F58634;
        --bs-btn-hover-color: #fff;
        --bs-btn-hover-bg: #F58634;
        --bs-btn-hover-border-color: #F58634;
        --bs-btn-active-color: #fff;
        --bs-btn-active-bg: #F58634;
        --bs-btn-active-border-color: #F58634;
        --bs-btn-focus-shadow-rgb: 245, 134, 52;
        white-space: nowrap;
    }
</style>

@if ($canChat)
<script>
    // コミュニティチャット：Chat タブを開いている間、5秒ごとに新着メッセージを取りに行く
    document.addEventListener('DOMContentLoaded', () => {
        const chat = document.querySelector('.community-chat');
        const pane = document.querySelector('#chat');
        const list = chat.querySelector('.community-chat-list');
        const empty = chat.querySelector('.community-chat-empty');
        const status = chat.querySelector('.community-chat-status');
        const form = chat.querySelector('.community-chat-form');
        const input = form.querySelector('textarea');
        const button = form.querySelector('button[type="submit"]');
        const groupWindow = 5 * 60 * 1000; // 同じ人の発言を1つにまとめる間隔
        const preferredLocale = Intl.DateTimeFormat.supportedLocalesOf(@json(app()->getLocale()))[0] || 'en';
        const todayLabel = @json(__('Today'));
        const yesterdayLabel = @json(__('Yesterday'));
        const messages = [];
        let editing = null; // 編集中のメッセージ { id, draft }
        let lastId = 0;
        let syncedAt = '';
        let loading = false;
        let loaded = false;
        const unreadBadge = document.querySelector('.community-chat-unread');
        const unreadFrom = document.querySelector('.community-chat-unread-from');
        let readId = 0;

        const dayLabel = (date) => {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            if (date.toDateString() === new Date().toDateString()) {
                return todayLabel;
            }
            if (date.toDateString() === yesterday.toDateString()) {
                return yesterdayLabel;
            }
            return date.toLocaleDateString(preferredLocale, { month: 'short', day: 'numeric', year: 'numeric' });
        };

        // 発言の位置に出す編集フォーム
        const renderEditor = () => {
            const editor = document.createElement('form');
            editor.className = 'community-chat-edit';
            const textarea = document.createElement('textarea');
            textarea.className = 'form-control';
            textarea.rows = 1;
            textarea.maxLength = 2000;
            textarea.value = editing.draft;
            textarea.setAttribute('aria-label', 'Edit message');
            const actions = document.createElement('div');
            actions.className = 'community-chat-edit-actions';
            const cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.className = 'btn btn-sm btn-outline-secondary community-chat-edit-cancel';
            cancel.textContent = 'Cancel';
            const save = document.createElement('button');
            save.type = 'submit';
            save.className = 'btn btn-sm community-chat-edit-save';
            save.textContent = 'Save';
            actions.append(cancel, save);
            editor.append(textarea, actions);

            return editor;
        };

        // 1件分の要素を作る（日が変わるときは日付の区切りも一緒に返す）
        const renderMessage = (message, previous) => {
            const sentAt = new Date(message.datetime);
            const previousAt = previous ? new Date(previous.datetime) : null;
            const newDay = !previousAt || previousAt.toDateString() !== sentAt.toDateString();
            const grouped = !newDay && previous.sender.id === message.sender.id && sentAt - previousAt < groupWindow;
            const nodes = [];

            if (newDay) {
                const day = document.createElement('li');
                day.className = 'community-chat-day';
                day.textContent = dayLabel(sentAt);
                nodes.push(day);
            }

            const item = document.createElement('li');
            item.className = 'community-chat-item';
            item.classList.toggle('community-chat-item--mine', message.is_mine);
            item.classList.toggle('community-chat-item--grouped', grouped);
            item.classList.toggle('community-chat-item--editing', editing?.id === message.id);
            item.dataset.messageId = message.id;

            // アイコンと名前からプロフィールを開ける（キーボード操作では名前のリンクだけを使う）
            const profileUrl = message.sender.profile_url;
            const avatar = document.createElement(profileUrl ? 'a' : 'span');
            avatar.className = 'community-chat-avatar text-decoration-none';
            if (profileUrl) {
                avatar.href = profileUrl;
                avatar.tabIndex = -1;
                avatar.setAttribute('aria-hidden', 'true');
            }
            if (message.sender.avatar) {
                const image = document.createElement('img');
                image.src = message.sender.avatar;
                image.alt = '';
                avatar.append(image);
            } else {
                avatar.textContent = message.sender.initial;
            }
            item.append(avatar);

            const content = document.createElement('div');
            content.className = 'community-chat-content';
            if (!grouped) {
                const name = document.createElement('div');
                name.className = 'community-chat-name';
                if (profileUrl) {
                    const link = document.createElement('a');
                    link.href = profileUrl;
                    link.textContent = message.sender.name;
                    name.append(link);
                } else {
                    name.textContent = message.sender.name;
                }
                content.append(name);
            }

            const row = document.createElement('div');
            row.className = 'community-chat-row';
            const bubble = document.createElement('div');
            bubble.className = 'community-chat-bubble';
            // 画像は押すと別のタブで元の大きさのまま開く
            if (message.image_url) {
                const imageLink = document.createElement('a');
                imageLink.className = 'community-chat-image';
                imageLink.href = message.image_url;
                imageLink.target = '_blank';
                imageLink.rel = 'noopener';
                const image = document.createElement('img');
                image.src = message.image_url;
                image.alt = `Photo from ${message.sender.name}`;
                image.loading = 'lazy';
                // 画像が読み込まれて高さが変わっても、一番下を見ていたら一番下のままにする
                image.addEventListener('load', () => {
                    if (list.scrollHeight - list.scrollTop - list.clientHeight < image.height + 60) {
                        list.scrollTop = list.scrollHeight;
                    }
                }, { once: true });
                imageLink.append(image);
                bubble.append(imageLink);
            }
            if (message.body) {
                const body = document.createElement('p');
                body.textContent = message.body;
                bubble.append(body);
                const translation = document.createElement('p');
                translation.className = 'community-chat-translation';
                translation.hidden = true;
                bubble.append(translation);
            }

            const side = document.createElement('div');
            side.className = 'community-chat-side';
            const time = document.createElement('time');
            time.textContent = sentAt.toLocaleTimeString(preferredLocale, { hour: 'numeric', minute: '2-digit' });
            time.dateTime = message.datetime;
            side.append(time);
            if (message.is_edited) {
                const edited = document.createElement('span');
                edited.textContent = 'edited';
                side.append(edited);
            }
            if (message.body) {
                const translate = document.createElement('button');
                translate.type = 'button';
                translate.className = 'community-chat-translate';
                translate.dataset.translateUrl = message.translate_url;
                translate.title = @json(__('Translate'));
                translate.setAttribute('aria-label', @json(__('Translate')));
                translate.innerHTML = '<i class="fa-solid fa-language" aria-hidden="true"></i>';
                side.append(translate);
            }
            if (message.can_edit) {
                const edit = document.createElement('button');
                edit.type = 'button';
                edit.className = 'community-chat-edit-button';
                edit.title = 'Edit';
                edit.setAttribute('aria-label', 'Edit message');
                edit.innerHTML = '<i class="fa-regular fa-pen-to-square"></i>';
                side.append(edit);
            }
            if (message.can_delete) {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'community-chat-delete';
                remove.dataset.deleteUrl = message.delete_url;
                remove.title = 'Delete';
                remove.setAttribute('aria-label', 'Delete message');
                remove.innerHTML = '<i class="fa-regular fa-trash-can"></i>';
                side.append(remove);
            }

            if (editing?.id === message.id) {
                row.append(renderEditor());
            } else {
                row.append(bubble, side);
            }
            content.append(row);
            item.append(content);
            nodes.push(item);

            return nodes;
        };

        const updateEmptyState = () => {
            empty.textContent = 'No messages yet. Say hello!';
            empty.hidden = messages.length > 0;
        };

        const appendMessage = (message) => {
            if (message.id <= lastId) {
                return;
            }
            lastId = message.id;
            list.append(...renderMessage(message, messages.at(-1)));
            messages.push(message);
        };

        // 消すと前後のまとまりや日付の区切りが変わるので、一覧を作り直す
        const removeMessage = (id) => {
            const index = messages.findIndex((message) => message.id === id);
            if (index === -1) {
                return;
            }
            messages.splice(index, 1);
            if (editing?.id === id) {
                editing = null;
            }
            list.replaceChildren(...messages.flatMap((message, i) => renderMessage(message, messages[i - 1])));
            focusEditor();
        };

        // 入力に合わせて入力欄を広げる（上限は CSS の max-height）
        const fitTextarea = (textarea) => {
            textarea.style.height = 'auto';
            textarea.style.height = `${textarea.scrollHeight + 2}px`;
        };
        const resizeInput = () => fitTextarea(input);

        // 1件だけ描き直す（他の発言の表示やスクロール位置はそのまま）
        const refreshMessage = (id) => {
            const index = messages.findIndex((message) => message.id === id);
            if (index !== -1) {
                list.querySelector(`[data-message-id="${id}"]`)
                    ?.replaceWith(renderMessage(messages[index], messages[index - 1]).at(-1));
            }
        };

        const focusEditor = () => {
            const textarea = list.querySelector('.community-chat-edit textarea');
            if (textarea) {
                fitTextarea(textarea);
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                textarea.scrollIntoView({ block: 'nearest' });
            }
        };

        const startEditing = (message) => {
            const previousId = editing?.id;
            editing = { id: message.id, draft: message.body };
            refreshMessage(previousId);
            refreshMessage(message.id);
            focusEditor();
        };

        const stopEditing = () => {
            const id = editing?.id;
            editing = null;
            refreshMessage(id);
            list.querySelector(`[data-message-id="${id}"] .community-chat-edit-button`)?.focus();
        };

        // 他の端末や他の人の編集を画面に反映する
        const applyUpdate = (updated) => {
            const index = messages.findIndex((message) => message.id === updated.id);
            if (index === -1 || (messages[index].body === updated.body && messages[index].is_edited === updated.is_edited)) {
                return;
            }
            messages[index] = updated;
            refreshMessage(updated.id);
        };

        const saveEdit = async (editor) => {
            const message = messages.find((candidate) => candidate.id === editing.id);
            const body = editor.querySelector('textarea').value.trim();
            // 画像付きのメッセージは文字を空にしてもよい
            if (!body && !message.image_url) {
                status.textContent = 'A message cannot be empty.';
                return;
            }
            if (body === message.body) {
                status.textContent = '';
                stopEditing();
                return;
            }
            const save = editor.querySelector('.community-chat-edit-save');
            save.disabled = true;
            try {
                const response = await fetch(message.update_url, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: body }),
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message);
                }
                messages[messages.indexOf(message)] = data.message;
                status.textContent = '';
                stopEditing();
            } catch (error) {
                status.textContent = error.message || 'Could not save the message.';
                save.disabled = false;
            }
        };

        const setUnreadBadge = (count, senders = []) => {
            unreadBadge.hidden = count === 0;
            unreadBadge.querySelector('.community-chat-unread-count').textContent = count > 99 ? '99+' : count;
            unreadFrom.hidden = senders.length === 0;
            unreadFrom.title = senders.join(', ');
            unreadFrom.querySelector('.community-chat-unread-name').textContent = senders[0] ?? '';
            unreadFrom.querySelector('.community-chat-unread-more').textContent = senders.length > 1 ? ` +${senders.length - 1}` : '';
        };

        // 別のタブを見ている間も未読数を更新する
        const loadUnread = async () => {
            try {
                const response = await fetch(chat.dataset.unreadUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (response.ok && !pane.classList.contains('active')) {
                    const data = await response.json();
                    setUnreadBadge(data.unread, data.senders);
                }
            } catch {
                // 次の更新でまた取りに行く
            }
        };

        // Chat タブで表示できたメッセージまでを既読にする（失敗したら次の更新でやり直す）
        const markRead = async () => {
            if (lastId <= readId || !pane.classList.contains('active')) {
                return;
            }
            readId = lastId;
            try {
                const response = await fetch(chat.dataset.readUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ last_read_id: lastId }),
                });
                if (!response.ok) {
                    throw new Error();
                }
                updateNavbarNotifications((await response.json()).community_notification_count);
            } catch {
                readId = 0;
            }
        };

        // 既読にしたら、ナビバーのコミュニティ通知の数字とこのコミュニティのチャット通知を再読み込みなしで消す
        const updateNavbarNotifications = (count) => {
            if (typeof count !== 'number') {
                return;
            }
            document.querySelectorAll(`[data-community-chat-notification="${chat.dataset.communityId}"]`)
                .forEach((item) => item.remove());

            const badge = document.querySelector('.js-community-notification-badge');
            if (badge && count > 0) {
                badge.textContent = count;
            } else {
                badge?.remove();
            }

            const notificationList = document.querySelector('.js-community-notification-list');
            if (notificationList && !notificationList.querySelector('.dropdown-content')
                && !notificationList.querySelector('.js-community-notification-empty')) {
                const emptyItem = document.createElement('li');
                emptyItem.className = 'js-community-notification-empty';
                emptyItem.innerHTML = '<span class="dropdown-item-text text-muted">No new community notifications</span>';
                notificationList.lastElementChild.before(emptyItem);
            }
        };

        const loadMessages = async () => {
            if (loading) {
                return;
            }
            loading = true;
            try {
                const query = new URLSearchParams({ after: lastId, since: syncedAt });
                const response = await fetch(`${chat.dataset.chatUrl}?${query}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    throw new Error();
                }
                const data = await response.json();
                const atBottom = list.scrollHeight - list.scrollTop - list.clientHeight < 40;
                data.messages.forEach(appendMessage);
                // 前回の取得以降に（自分や他の人が）消したメッセージを画面からも消す
                data.deleted_ids.forEach(removeMessage);
                data.updated_messages.forEach(applyUpdate);
                syncedAt = data.synced_at;
                // 過去の発言を読み返しているときは勝手にスクロールしない
                if (data.messages.length && (atBottom || !loaded)) {
                    list.scrollTop = list.scrollHeight;
                }
                loaded = true;
                updateEmptyState();
                status.textContent = '';
                markRead();
            } catch {
                empty.hidden = true;
                status.textContent = 'Could not load messages.';
            } finally {
                loading = false;
            }
        };

        // 画像の添付（選んだら送る前に小さく表示し、× で取り消せる）
        const fileInput = form.querySelector('input[type="file"]');
        const attachment = chat.querySelector('.community-chat-attachment');
        const maxImageSize = 5 * 1024 * 1024;
        let previewUrl = null;
        const clearAttachment = () => {
            fileInput.value = '';
            attachment.hidden = true;
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
        };
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) {
                clearAttachment();
                return;
            }
            if (!file.type.startsWith('image/')) {
                clearAttachment();
                status.textContent = 'Please choose an image file.';
                return;
            }
            if (file.size > maxImageSize) {
                clearAttachment();
                status.textContent = 'Photos must be 5 MB or smaller.';
                return;
            }
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
            previewUrl = URL.createObjectURL(file);
            attachment.querySelector('img').src = previewUrl;
            attachment.querySelector('.community-chat-attachment-name').textContent = file.name;
            attachment.hidden = false;
            status.textContent = '';
            input.focus();
        });
        attachment.querySelector('.community-chat-attachment-remove').addEventListener('click', () => {
            clearAttachment();
            input.focus();
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const message = input.value.trim();
            const image = fileInput.files[0];
            if (!message && !image) {
                return;
            }
            // 画像があるときはファイルごと送れる FormData で送る
            const payload = new FormData();
            payload.append('message', message);
            if (image) {
                payload.append('image', image);
            }
            button.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: payload,
                });
                const data = await response.json();
                if (!response.ok) {
                    // 入力の確認で弾かれたときは、最初のエラーの文を出す
                    throw new Error(Object.values(data.errors ?? {}).flat()[0] ?? data.message);
                }
                clearAttachment();
                // 送信の間に届いた他の人のメッセージを先に取り込んでから自分の分を足す
                await loadMessages();
                appendMessage(data.message);
                markRead();
                list.scrollTop = list.scrollHeight;
                input.value = '';
                resizeInput();
                updateEmptyState();
                status.textContent = '';
            } catch (error) {
                status.textContent = error.message || 'Could not send message.';
            } finally {
                button.disabled = false;
                input.focus();
            }
        });

        // 編集の開始と取り消し
        list.addEventListener('click', (event) => {
            if (event.target.closest('.community-chat-edit-cancel')) {
                status.textContent = '';
                stopEditing();
                return;
            }
            const edit = event.target.closest('.community-chat-edit-button');
            if (edit) {
                const id = Number(edit.closest('[data-message-id]').dataset.messageId);
                startEditing(messages.find((message) => message.id === id));
            }
        });

        list.addEventListener('click', async (event) => {
            const translate = event.target.closest('.community-chat-translate');
            if (!translate || translate.disabled) {
                return;
            }
            const item = translate.closest('[data-message-id]');
            const result = item?.querySelector('.community-chat-translation');
            if (!item || !result) {
                return;
            }

            translate.disabled = true;
            status.textContent = @json(__('Searching...'));
            try {
                const response = await fetch(translate.dataset.translateUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || @json(__('Translation is unavailable right now.')));
                }
                result.textContent = data.translation;
                result.hidden = false;
                status.textContent = '';
            } catch (error) {
                status.textContent = error.message || @json(__('Translation is unavailable right now.'));
            } finally {
                translate.disabled = false;
            }
        });

        list.addEventListener('submit', (event) => {
            event.preventDefault();
            saveEdit(event.target);
        });

        list.addEventListener('input', (event) => {
            if (editing && event.target.matches('.community-chat-edit textarea')) {
                editing.draft = event.target.value;
                fitTextarea(event.target);
            }
        });

        // 編集欄では Enter で保存、Shift+Enter で改行、Esc で取り消し
        list.addEventListener('keydown', (event) => {
            if (!event.target.matches('.community-chat-edit textarea')) {
                return;
            }
            if (event.key === 'Escape') {
                status.textContent = '';
                stopEditing();
            } else if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                event.target.form.requestSubmit();
            }
        });

        // ゴミ箱ボタンでメッセージを削除（本人かオーナーだけに表示される）
        list.addEventListener('click', async (event) => {
            const remove = event.target.closest('.community-chat-delete');
            if (!remove || !confirm('Delete this message?')) {
                return;
            }
            remove.disabled = true;
            try {
                const response = await fetch(remove.dataset.deleteUrl, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                if (!response.ok) {
                    throw new Error();
                }
                removeMessage((await response.json()).deleted);
                updateEmptyState();
                status.textContent = '';
            } catch {
                remove.disabled = false;
                status.textContent = 'Could not delete the message.';
            }
        });

        // Enter で送信、Shift+Enter で改行
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        input.addEventListener('input', resizeInput);

        document.querySelector('#chat-tab').addEventListener('shown.bs.tab', () => {
            // カバー画像の下でも入力欄まで画面に入るようにする
            chat.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            setUnreadBadge(0);
            loadMessages();
        });
        setInterval(() => {
            if (document.visibilityState !== 'visible') {
                return;
            }
            if (pane.classList.contains('active')) {
                loadMessages();
            } else {
                loadUnread();
            }
        }, 5000);

    });
</script>
@endif
<script>
    // #chat や #members 付きで開いたら（承認・送信後のリダイレクトなど）そのタブを表示する
    document.addEventListener('DOMContentLoaded', () => {
        const tab = /^#[a-z]+$/.test(location.hash) && document.querySelector(`#communityTab [data-bs-target="${location.hash}"]`);
        if (tab) {
            tab.click();
        }
    });
</script>
@endsection