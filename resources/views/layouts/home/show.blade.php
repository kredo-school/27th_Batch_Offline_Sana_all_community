@extends('layouts.app')

@php
    $translatedTitle = match ($title) {
        'Notifications' => __('Notifications'),
        'Messages' => __('Messages'),
        'Community' => __('Communities'),
        default => $title,
    };
    $isNotificationList = in_array($title, ['Notifications', 'Community'], true);
@endphp

@section('title', __('Show') . ' ' . $translatedTitle)

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
            min-height: calc(100vh - 70px);
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
            grid-column: 2;
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

        @media (max-width: 760px) {
            .show-section-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .show-section-main {
                grid-column: 1;
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
            <p class="text-uppercase small fw-bold text-success mb-1">{{ __('View All') }}</p>
            <h1 class="h3 mb-0">{{ $translatedTitle }}</h1>
            <p class="text-secondary small mt-2 mb-0">{{ __('records', ['count' => $records->total()]) }}</p>
        </header>
        @if (session('status'))
            <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
        @endif
        @if ($title === 'Notifications')
            <ul class="mb-3">
                <a href="{{ route('notifications.index') }}" class="btn btn-white text-danger border-danger rounded me-0 ms-0 btn-noti btn-outline-danger btn-custom-outline">{{ __('All') }}</a>
                <a href="{{ route('notifications.index', ['type' => 'general']) }}" class="btn btn-white text-primary border-primary rounded me-0 ms-0 btn-noti btn-outline-primary btn-custom-outline">{{ __('General') }}</a>
                <a href="{{ route('notifications.index', ['type' => 'maintenance']) }}" class="btn btn-white text-warning border-warning rounded me-0 ms-0 btn-noti btn-outline-warning btn-custom-outline">{{ __('Maintenance') }}</a>
                <a href="{{ route('notifications.index', ['type' => 'community']) }}" class="btn btn-white text-success border-success rounded ms-0 btn-noti btn-outline-success btn-custom-outline fs-6">{{ __('Communities') }}</a>
            </ul>
        @elseif($title === 'Messages')
            <button type="button" class="btn btn-outline-primary btn-white text-primary btn-custom-outline mb-3 ms-4 btn-noti" data-bs-toggle="modal"
                data-bs-target="#create-message-modal">{{ __('Create Message') }}</button>
        @endif
        <div class="show-section-table-wrap">
                <table class="table show-section-table align-middle table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th></th>
                            @foreach ($columns as $label => $key)
                                <th>{{ __($label) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            @php
                                $chatParticipant = $title === 'Messages'
                                    ? ((int) $record->sender_id === (int) Auth::id() ? $record->receiver : $record->sender)
                                    : null;
                                $isUnread = $isNotificationList
                                    ? $record->read_at === null
                                    : $chatParticipant !== null
                                        && in_array((int) $chatParticipant->id, $unreadMessageSenderIds ?? [], true);
                                $showUrl = ! $isNotificationList
                                    ? null
                                    : ($record->type === 'community_message' && $record->related_id
                                        ? route('community.show', $record->related_id).'#chat'
                                        : route('notifications.show', $record));
                            @endphp
                            <tr class="{{ trim(($isUnread ? 'unread-row ' : '') . ($chatParticipant ? 'js-open-message-chat message-list-row' : '')) }}"
                                @if ($chatParticipant)
                                    data-message-chat-url="{{ route('messages.conversation', $chatParticipant->id) }}"
                                    data-message-chat-send-url="{{ route('messages.send', $chatParticipant->id) }}"
                                    data-message-chat-name="{{ $chatParticipant->username ?: __('Unknown user') }}"
                                    role="button" tabindex="0"
                                @elseif ($showUrl)
                                    style="cursor: pointer;"
                                    onclick="if(event.target.closest('a, button, form, input')) return; window.location.href='{{ $showUrl }}';"
                                @endif>
                                @if ($chatParticipant)
                                    @php
                                        $participantName = $chatParticipant->username ?: __('Unknown user');
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
                                    @if ($isNotificationList && $key === 'title')
                                        <td>
                                            @php
                                                $notificationUrl = $record->type === 'community_message' && $record->related_id
                                                    ? route('community.show', $record->related_id).'#chat'
                                                    : route('notifications.show', $record);
                                            @endphp
                                            <a href="{{ $notificationUrl }}" class="text-decoration-none text-dark">{{ $value ?: $record->type_label }}</a>
                                        </td>
                                    @else
                                        <td>{{ $value instanceof \DateTimeInterface ? \App\Support\LocalizedValue::dateTime($value) : (is_string($value) ? \Illuminate\Support\Str::limit($value, 120) : ($value ?? '-')) }}</td>
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
                                                    data-confirm-message="{{ __('Block this user? You will not be able to message each other.') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm btn-custom-outline">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('messages.destroy', $record) }}" method="POST"
                                                data-confirm="true"
                                                data-confirm-message="{{ __('Delete this message?') }}">
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
                                    {{ __('No :title to display.', ['title' => Str::lower($translatedTitle)]) }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
        </div>

        <div class="d-flex justify-content-center align-items-center gap-3 mt-3 flex-wrap">
            {{ $records->withQueryString()->links() }}
        </div>
    </section>
</div>
@endsection
    