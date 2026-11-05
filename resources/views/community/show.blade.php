@extends('layouts.app-with-event-sidebar')

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
        <!-- Cover Banner (背景カバー画像エリア：写真があれば写真の縦横比に合わせて枠の高さが変わる) -->
        <div class="position-relative d-flex align-items-center justify-content-center overflow-hidden {{ $community->image ? 'bg-white' : 'bg-light community-cover-empty' }}">
            @if ($community->image)
                <img src="{{ asset('storage/' . $community->image) }}" alt="{{ $community->name }} cover image" class="d-block w-100 object-fit-contain" style="max-height: 260px;">
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
                        <a href="{{ route('community.events.create', $community->id) }}" class="btn btn-primary btn-sm text-white fw-bold px-3">+ Post an event</a>
                    @elseif ($membershipStatus === 'approved')
                        <span class="badge rounded-pill community-joined-badge px-3 py-2"><i class="fa-solid fa-check me-1"></i> Joined</span>
                        <form action="{{ route('community.leave', $community->id) }}" method="POST"
                            data-confirm="true" data-confirm-message="Leave this community?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold px-3">Leave</button>
                        </form>
                    @elseif ($membershipStatus === 'pending')
                        <span class="badge rounded-pill bg-light text-secondary border px-3 py-2">Request pending</span>
                        <form action="{{ route('community.leave', $community->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm fw-bold px-3">Cancel request</button>
                        </form>
                    @elseif ($community->visibility === 'private')
                        <span class="badge rounded-pill bg-light text-secondary border px-3 py-2"><i class="fa-solid fa-lock me-1"></i> Invite only</span>
                    @else
                        <form action="{{ route('community.join', $community->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm text-white fw-bold px-3">
                                {{ $community->visibility === 'approval' ? 'Request to join' : '+ Join community' }}
                            </button>
                        </form>
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
            <div class="card bg-white border shadow-sm rounded-3 p-3 mb-4">
                @forelse ($community->events as $event)
                    <x-event-row :event="$event" class="py-3 border-bottom">
                        <a href="{{ route('events.show', $event->id) }}" class="btn btn-outline-secondary btn-sm text-nowrap">View details &gt;</a>
                    </x-event-row>
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
                <x-event-row :event="$event" show-description class="py-3 border-bottom">
                    <a href="{{ route('events.show', $event->id) }}" class="btn btn-outline-secondary btn-sm text-nowrap">View details &gt;</a>
                </x-event-row>
            @empty
                <p class="text-muted small mb-0">There are no upcoming events for this community yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Chat タブ: オーナーと承認済みメンバーだけが使える -->
    <div class="tab-pane fade" id="chat" role="tabpanel" aria-labelledby="chat-tab">
        @if ($canChat)
            <div class="card bg-white border shadow-sm rounded-3 overflow-hidden community-chat"
                data-chat-url="{{ route('community.chat.index', $community->id) }}">
                <div class="community-chat-status small text-muted px-3 pt-3" role="status" aria-live="polite">Loading messages...</div>
                <ol class="community-chat-list" aria-live="polite"></ol>
                <form action="{{ route('community.chat.store', $community->id) }}" method="POST" class="community-chat-form">
                    @csrf
                    <textarea name="message" class="form-control" rows="1" maxlength="2000" required
                        placeholder="Write a message to {{ $community->name }}" aria-label="Write a message"></textarea>
                    <button type="submit" class="btn btn-primary" aria-label="Send message">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        @else
            <div class="card bg-white border shadow-sm rounded-3 p-4 text-center">
                <p class="text-muted small mb-0">Only members of this community can join the chat.</p>
            </div>
        @endif
    </div>

    <!-- Members タブ: オーナー + 承認済みメンバー -->
    <div class="tab-pane fade" id="members" role="tabpanel" aria-labelledby="members-tab">
        {{-- 参加申請（オーナーだけに表示） --}}
        @if ($pendingMembers->isNotEmpty())
            <div class="card bg-white border shadow-sm rounded-3 p-3 mb-3 community-requests">
                <h6 class="fw-bold text-dark mb-2">Join requests <span class="badge rounded-pill community-joined-badge">{{ $pendingMembers->count() }}</span></h6>
                @foreach ($pendingMembers as $requester)
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                        <x-avatar :user="$requester" />
                        <strong class="text-dark small flex-grow-1">{{ $requester->name }}</strong>
                        <form action="{{ route('community.members.approve', [$community->id, $requester->id]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary btn-sm text-white">Approve</button>
                        </form>
                        <form action="{{ route('community.members.remove', [$community->id, $requester->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Decline</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card bg-white border shadow-sm rounded-3 p-3 community-member-list">
            @foreach (collect([$community->owner])->filter()->merge($community->members->where('id', '!=', $community->owner_id)) as $member)
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <x-avatar :user="$member" />
                    <div class="flex-grow-1">
                        <strong class="d-block text-dark small">{{ $member->name }}</strong>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            {{ $member->id === $community->owner_id ? 'Community organizer' : ucfirst($member->pivot->role ?? 'member') }}
                        </small>
                    </div>
                    @if (auth()->id() === $community->owner_id && $member->id !== $community->owner_id)
                        <form action="{{ route('community.members.remove', [$community->id, $member->id]) }}" method="POST"
                            data-confirm="true" data-confirm-message="Remove {{ $member->name }} from this community?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none">Remove</button>
                        </form>
                    @endif
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

<style>
    .community-chat-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        height: 420px;
        overflow-y: auto;
        margin: 0;
        padding: 16px;
        list-style: none;
    }

    .community-chat-item {
        display: flex;
        gap: 8px;
        max-width: 80%;
        align-self: flex-start;
    }

    .community-chat-item--mine {
        flex-direction: row-reverse;
        align-self: flex-end;
    }

    .community-chat-avatar {
        display: grid;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        place-items: center;
        overflow: hidden;
        border-radius: 50%;
        background: #F58634;
        color: #fff;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .community-chat-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .community-chat-bubble {
        padding: 8px 12px;
        border-radius: 12px 12px 12px 2px;
        background: #f1f3f5;
        overflow-wrap: anywhere;
    }

    .community-chat-item--mine .community-chat-bubble {
        border-radius: 12px 12px 2px 12px;
        background: #fdebdc;
    }

    .community-chat-meta {
        margin-bottom: 2px;
        color: #6c757d;
        font-size: 0.72rem;
    }

    .community-chat-meta strong {
        color: #212529;
        margin-right: 6px;
    }

    .community-chat-bubble p {
        margin: 0;
        white-space: pre-wrap;
    }

    .community-chat-form {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding: 12px;
        border-top: 1px solid #e9ecef;
    }

    .community-chat-form textarea {
        min-height: 40px;
        max-height: 120px;
        resize: vertical;
    }

    .community-chat-form textarea:focus {
        border-color: #F58634;
        box-shadow: 0 0 0 0.25rem rgba(245, 134, 52, 0.25);
    }

    .community-chat-delete {
        margin-left: 6px;
        padding: 0;
        border: 0;
        background: transparent;
        color: #adb5bd;
        font-size: 0.72rem;
    }

    .community-chat-delete:hover {
        color: #dc3545;
    }

    /* 参加中バッジと参加申請の数 */
    .community-joined-badge {
        background: #fdebdc;
        color: #F58634;
    }
</style>

@if ($canChat)
<script>
    // コミュニティチャット：Chat タブを開いている間、5秒ごとに新着メッセージを取りに行く
    document.addEventListener('DOMContentLoaded', () => {
        const chat = document.querySelector('.community-chat');
        const pane = document.querySelector('#chat');
        const list = chat.querySelector('.community-chat-list');
        const status = chat.querySelector('.community-chat-status');
        const form = chat.querySelector('.community-chat-form');
        const input = form.querySelector('textarea');
        const button = form.querySelector('button[type="submit"]');
        let lastId = 0;
        let syncedAt = '';
        let loading = false;

        const appendMessage = (message) => {
            if (message.id <= lastId) {
                return;
            }
            lastId = message.id;

            const item = document.createElement('li');
            item.className = message.is_mine ? 'community-chat-item community-chat-item--mine' : 'community-chat-item';

            const avatar = document.createElement('span');
            avatar.className = 'community-chat-avatar';
            if (message.sender.avatar) {
                const image = document.createElement('img');
                image.src = message.sender.avatar;
                image.alt = '';
                avatar.append(image);
            } else {
                avatar.textContent = message.sender.initial;
            }

            const bubble = document.createElement('div');
            bubble.className = 'community-chat-bubble';
            const meta = document.createElement('div');
            meta.className = 'community-chat-meta';
            const name = document.createElement('strong');
            name.textContent = message.sender.name;
            const time = document.createElement('time');
            time.textContent = message.sent_at;
            time.dateTime = message.datetime;
            meta.append(name, time);
            if (message.can_delete) {
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'community-chat-delete';
                remove.dataset.deleteUrl = message.delete_url;
                remove.setAttribute('aria-label', 'Delete message');
                remove.innerHTML = '<i class="fa-regular fa-trash-can"></i>';
                meta.append(remove);
            }
            const body = document.createElement('p');
            body.textContent = message.body;
            bubble.append(meta, body);

            item.dataset.messageId = message.id;
            item.append(avatar, bubble);
            list.append(item);
        };

        const removeMessage = (id) => {
            list.querySelector(`[data-message-id="${id}"]`)?.remove();
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
                syncedAt = data.synced_at;
                if (data.messages.length && (atBottom || lastId === data.messages.at(-1).id)) {
                    list.scrollTop = list.scrollHeight;
                }
                status.textContent = list.children.length ? '' : 'No messages yet. Say hello!';
            } catch {
                status.textContent = 'Could not load messages.';
            } finally {
                loading = false;
            }
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const message = input.value.trim();
            if (!message) {
                return;
            }
            button.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message }),
                });
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message);
                }
                // 送信の間に届いた他の人のメッセージを先に取り込んでから自分の分を足す
                await loadMessages();
                appendMessage(data.message);
                list.scrollTop = list.scrollHeight;
                input.value = '';
                status.textContent = '';
            } catch (error) {
                status.textContent = error.message || 'Could not send message.';
            } finally {
                button.disabled = false;
                input.focus();
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
                status.textContent = list.children.length ? '' : 'No messages yet. Say hello!';
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

        document.querySelector('#chat-tab').addEventListener('shown.bs.tab', loadMessages);
        setInterval(() => {
            if (pane.classList.contains('active') && document.visibilityState === 'visible') {
                loadMessages();
            }
        }, 5000);

    });
</script>
@endif
<script>
    // #chat や #members 付きで開いたら（承認・送信後のリダイレクトなど）そのタブを表示する
    document.addEventListener('DOMContentLoaded', () => {
        const tab = /^#[a-z]+$/.test(location.hash) && document.querySelector(`#communityTab [data-bs-target="${location.hash}"]`);
        tab?.click();
    });
</script>
@endsection