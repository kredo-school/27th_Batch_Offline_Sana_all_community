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

        .message-list-row:hover,
        .message-list-row:focus-visible {
            outline: 2px solid #4b9b87;
            outline-offset: -2px;
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
        @include('partials.sidebar')

        <section class="show-section-main">
            <header class="mb-4">
                <p class="text-uppercase small fw-bold text-success mb-1">View All</p>
                <h1 class="h3 mb-0">{{ $title }}</h1>
                <p class="text-secondary small mt-2 mb-0">{{ $records->total() }} records</p>
               
            </header>
            @if ($title === 'Notifications')
            <ul class="mb-3">
                <a href="{{ route('notifications.index') }}" class="btn btn-danger text-white me-0 ms-0 btn-noti">All</a>
                <a href="{{ route('notifications.index', ['type' => 'general']) }}" class="btn btn-primary text-white me-0 ms-0 btn-noti">General</a>
                <a href="{{ route('notifications.index', ['type' => 'maintenance']) }}" class="btn btn-warning text-white me-0 ms-0 btn-noti">Maintenance</a>
                <a href="{{ route('notifications.index', ['type' => 'community']) }}" class="btn btn-success text-white ms-0 btn-noti">Community</a>
            </ul>
            @else
                
            @endif
            <div class="show-section-table-wrap">
                <table class="table show-section-table align-middle">
                    <thead class="bg-primary text-white">
                        <tr>
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
                            @endphp
                            <tr @if ($chatParticipant)
                                class="js-open-message-chat message-list-row"
                                data-message-chat-url="{{ route('messages.conversation', $chatParticipant->id) }}"
                                data-message-chat-send-url="{{ route('messages.send', $chatParticipant->id) }}"
                                data-message-chat-name="{{ $chatParticipant->username ?? $chatParticipant->name ?? __('Unknown user') }}"
                                role="button" tabindex="0"
                            @endif>
                                @foreach ($columns as $key)
                                    @php
                                        $value = data_get($record, $key);
                                    @endphp
                                    @if ($title === 'Notifications' && $key === 'title')
                                        <td><a href="{{ route('notifications.show', $record) }}">{{ $value ?: '-' }}</a></td>
                                    @else
                                    <td>{{ $value instanceof \DateTimeInterface ? $value->format('M d, Y H:i') : (is_string($value) ? \Illuminate\Support\Str::limit($value, 120) : ($value ?? '-')) }}</td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="text-center text-secondary py-4">
                                    No {{ strtolower($title) }} to display.
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