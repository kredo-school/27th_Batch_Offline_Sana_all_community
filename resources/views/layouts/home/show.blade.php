@extends('layouts.app')

@section('title', 'Show' . $title)

@section('content')
    <style>
        .btn-noti{
            width: 200px;
        }
        .show-section-layout {
            --show-ink: #173d3d;
            --show-muted: #647b79;
            --show-line: #d8e7e4;
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: calc(100vh - 74px);
            color: var(--show-ink);
            background: #f5f8f7;
        }

        .show-section-layout .communication-sidebar {
            padding: 24px 14px;
            border-right: 1px solid var(--show-line);
            background: #fff;
        }

        .show-section-layout .communication-sidebar-title {
            margin: 0 10px 16px;
            color: var(--show-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .show-section-layout .communication-sidebar-nav {
            display: grid;
            gap: 4px;
        }

        .show-section-layout .communication-sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 40px;
            padding: 0 11px;
            border-radius: 5px;
            color: var(--show-ink);
            font-size: 14px;
            text-decoration: none;
        }

        .show-section-layout .communication-sidebar-link i {
            width: 17px;
            color: #668380;
            text-align: center;
        }

        .show-section-layout .communication-sidebar-link:hover {
            color: #126b6b;
            background: #d9f2ef;
        }

        .show-section-main {
            min-width: 0;
            padding: 28px clamp(16px, 3vw, 42px) 36px;
        }

        .show-section-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--show-line);
            border-radius: 5px;
            background: #fff;
        }

        .show-section-table {
            min-width: 640px;
            margin: 0;
        }

        .show-section-table tbody td {
            background-color: transparent !important;
        }

        .message-list-row {
            cursor: pointer;
        }

        .show-section-table tbody tr.unread-row td {
            background-color: #e3f2ec !important;
        }

        .show-section-table tbody tr.unread-row:hover td {
            background-color: #cfe8dc !important;
        }

         .table-hover tbody tr:hover td, .table-hover tbody tr:hover th {
            color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .btn-custom-outline:hover,
        .btn-custom-outline:active {
            color: #ffffff !important;
        }

        .community-summary-row {
            border: 1px solid var(--show-line);
            border-radius: 5px;
            color: var(--show-ink);
            background: #fff;
        }

        .community-summary-row:hover {
            color: #126b6b;
            background: #f0faf8;
        }

        .community-summary-row--unread {
            border-color: #b7dfc8;
            background: #e3f2ec;
        }

        .community-summary-row--unread:hover {
            background: #cfe8dc;
        }

        @media (max-width: 760px) {
            .show-section-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .show-section-layout .communication-sidebar {
                padding: 10px 12px;
                border-right: 0;
                border-bottom: 1px solid var(--show-line);
            }

            .show-section-layout .communication-sidebar-title {
                margin-bottom: 8px;
            }

            .show-section-layout .communication-sidebar-nav {
                display: flex;
                overflow-x: auto;
            }

            .show-section-layout .communication-sidebar-link {
                flex: 0 0 auto;
            }
        }
    </style>

    <div class="show-section-layout">
        @include('layouts.home.partials.top-sidebar')

        <section class="show-section-main">
            <header class="mb-4">
                <p class="text-uppercase small fw-bold text-success mb-1">View All</p>
                <h1 class="h3 mb-0">{{ $title }}</h1>
                @if (!($title === 'Notifications' && request('type') === 'community'))
                    <p class="text-secondary small mt-2 mb-0">{{ $records->total() }} records</p>
                @endif
            </header>
            @if (session('status'))
                <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
            @endif
            @if ($title === 'Notifications')
            @if (request('type') === 'community')
                <div class="community-summary-list">
                    @foreach ([['title' => 'Owned Communities', 'communities' => $ownedCommunities], ['title' => 'Joined Communities', 'communities' => $joinedCommunities]] as $section)
                        <section class="community-summary-section mb-4" aria-labelledby="{{ str($section['title'])->slug() }}-title">
                            <h2 id="{{ str($section['title'])->slug() }}-title" class="h5 mb-3">{{ $section['title'] }}</h2>
                            @forelse ($section['communities'] as $community)
                                <a href="{{ route('community.show', $community->id) }}#chat"
                                    class="community-summary-row d-flex align-items-center justify-content-between gap-3 p-3 mb-2 text-decoration-none {{ $community->unread_chat_messages > 0 ? 'community-summary-row--unread' : '' }}">
                                    <span class="fw-semibold">{{ $community->name }}</span>
                                    <span class="d-flex flex-wrap justify-content-end gap-2 small text-nowrap">
                                        <span>{{ $community->total_chat_messages }} chat messages</span>
                                        <span class="{{ $community->unread_chat_messages > 0 ? 'fw-bold text-danger' : 'text-muted' }}">
                                            {{ $community->unread_chat_messages }} unread chats
                                        </span>
                                    </span>
                                </a>
                            @empty
                                <p class="text-secondary small">You have no {{ strtolower($section['title']) }}.</p>
                            @endforelse
                        </section>
                    @endforeach
                </div>
            @else
            <ul class="mb-3">
                <a href="{{ route('notifications.index') }}" class="btn btn-white text-danger border-danger rounded me-0 ms-0 btn-noti btn-outline-danger btn-custom-outline">All</a>
                <a href="{{ route('notifications.index', ['type' => 'general']) }}" class="btn btn-white text-primary border-primary rounded me-0 ms-0 btn-noti btn-outline-primary btn-custom-outline">General</a>
                <a href="{{ route('notifications.index', ['type' => 'maintenance']) }}" class="btn btn-white text-warning border-warning rounded me-0 ms-0 btn-noti btn-outline-warning btn-custom-outline">Maintenance</a>
                <a href="{{ route('notifications.index', ['type' => 'community']) }}" class="btn btn-white text-success border-success rounded ms-0 btn-noti btn-outline-success btn-custom-outline fs-6">Communities</a>
            </ul>
            @endif
            @elseif($title === 'Messages')
                    <button type="button" class="btn btn-outline-primary btn-white text-primary btn-custom-outline mb-3 ms-4 btn-noti" data-bs-toggle="modal"
                        data-bs-target="#create-message-modal">Create Message</button>
                    
            @endif
            @if (!($title === 'Notifications' && request('type') === 'community'))
            <div class="show-section-table-wrap">
                <table class="table show-section-table align-middle table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th></th>
                            @foreach ($columns as $label => $key)
                                <th>{{ $label }}</th>
                            @endforeach
                           
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $chatParticipant = $title === 'Messages'
                                    ? ((int) $record->sender_id === (int) Auth::id() ? $record->receiver : $record->sender)
                                    : null;
                                $isUnread = $title === 'Notifications'
                                    ? $record->read_at === null
                                    : $chatParticipant !== null
                                        && in_array((int) $chatParticipant->id, $unreadMessageSenderIds ?? [], true);
                            @endphp
                                @php
                                        $showUrl = match ($title) {
                                            'Notifications' => route('notifications.show', $record),
                                           
                                            // 'Messages' => route('messages.show', $record),
                                            
                                            default => null,
                                        };
                                @endphp
                            <tr class="{{ $isUnread ? 'unread-row' : '' }}"
                                @if ($chatParticipant)
                                    class="js-open-message-chat message-list-row"
                                    data-message-chat-url="{{ route('messages.conversation', $chatParticipant->id) }}"
                                    data-message-chat-send-url="{{ route('messages.send', $chatParticipant->id) }}"
                                    data-message-chat-name="{{ $chatParticipant->username ?? $chatParticipant->name ?? __('Unknown user') }}"
                                    role="button" tabindex="0"
                                @elseif ($showUrl)
                                    style="cursor: pointer;"
                                    onclick="if(event.target.closest('a, button, form, input')) return; window.location.href='{{ $showUrl }}';"
                                @endif>
                                @if ($chatParticipant)
                                    @php
                                        $participantName = $chatParticipant->username ?? $chatParticipant->name ?? __('Unknown user');
                                        $participantAvatar = $chatParticipant->profile?->avatar;
                                    @endphp
                                    <td>
                                        <span class="message-chat-avatar" aria-label="{{ $participantName }}">
                                            @if ($participantAvatar)
                                                @php
                                                    $participantAvatarUrl = str_starts_with($participantAvatar, 'https://') || str_starts_with($participantAvatar, 'http://')
                                                        ? $participantAvatar
                                                        : asset('storage/' . ltrim($participantAvatar, '/'));
                                                @endphp
                                                <img src="{{ $participantAvatarUrl }}" alt="{{ $participantName }} avatar">
                                            @else
                                                {{ strtoupper(substr($participantName, 0, 1)) }}
                                            @endif
                                        </span>
                                    </td>
                                @else
                                    <td></td>
                                @endif
                                @foreach ($columns as $key)
                                    @php
                                        $value = data_get($record, $key);
                                    @endphp
                                    @if ($title === 'Notifications' && $key === 'title')
                                        <td><a href="{{ route('notifications.show', $record) }}" class="text-decoration-none text-dark">{{ $value ?: '-' }}</a></td>
                                    @else
                                    <td>{{ $value instanceof \DateTimeInterface ? $value->format('M d, Y H:i') : (is_string($value) ? \Illuminate\Support\Str::limit($value, 120) : ($value ?? '-')) }}</td>
                                    @endif
                                @endforeach
                                @if ($chatParticipant)
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if (in_array((int) $chatParticipant->id, $blockedUserIds ?? [], true))
                                                <form action="{{ route('messages.unblock', $chatParticipant) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="fa-regular fa-square-plus"></i></button>
                                                </form>
                                            @else
                                                <form action="{{ route('messages.block', $chatParticipant) }}" method="POST"
                                                    data-confirm="true"
                                                    data-confirm-message="Block this user? You will not be able to message each other.">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm btn-custom-outline">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('messages.destroy', $record) }}" method="POST"
                                                data-confirm="true"
                                                data-confirm-message="Delete this message?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + ($title === 'Messages' ? 1 : 0) }}" class="text-center text-secondary py-4">
                                    No {{ strtolower($title) }} to display.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif

            @if (!($title === 'Notifications' && request('type') === 'community'))
                <div class="d-flex justify-content-center align-items-center gap-3 mt-3 flex-wrap">
                    {{ $records->withQueryString()->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection