@extends('layouts.app')

@section('title', 'Admin ' . $title)

@section('content')
    <style>
        .admin-section-layout {
            --admin-ink: #173d3d;
            --admin-muted: #647b79;
            --admin-line: #d8e7e4;
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: calc(100vh - 74px);
            color: var(--admin-ink);
            background: #f5f8f7;
        }

        .admin-section-layout .admin-sidebar {
            padding: 24px 14px;
            border-right: 1px solid var(--admin-line);
            background: #fff;
        }

        .admin-section-layout .admin-sidebar-title {
            margin: 0 10px 16px;
            color: var(--admin-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-section-layout .admin-nav {
            display: grid;
            gap: 4px;
        }

        .admin-section-layout .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 40px;
            padding: 0 11px;
            border-radius: 5px;
            color: var(--admin-ink);
            font-size: 14px;
            text-decoration: none;
        }

        .admin-section-layout .admin-nav-link i {
            width: 17px;
            color: #668380;
            text-align: center;
        }

        .admin-section-layout .admin-nav-link:hover,
        .admin-section-layout .admin-nav-link.active {
            color: #126b6b;
            background: #d9f2ef;
        }

        .admin-section-layout .admin-nav-link.active {
            font-weight: 700;
        }

        .admin-section-main {
            min-width: 0;
            padding: 28px clamp(16px, 3vw, 42px) 36px;
        }

        .admin-section-table-wrap {
            overflow-x: auto;
            border: 1px solid var(--admin-line);
            border-radius: 5px;
            background: #fff;
        }

        .admin-section-table {
            min-width: 640px;
            margin: 0;
        }

        /* .admin-section-table thead th {
            color: #103f40;
            background: #35c6c5;
            font-size: 11px;
            text-transform: uppercase;
        } */

        .admin-section-table tbody td {
            background-color: transparent !important;
        }

        .btn-noti{
            width:150px;
        }

        @media (max-width: 760px) {
            .admin-section-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .admin-section-layout .admin-sidebar {
                padding: 10px 12px;
                border-right: 0;
                border-bottom: 1px solid var(--admin-line);
            }

            .admin-section-layout .admin-sidebar-title {
                margin-bottom: 8px;
            }

            .admin-section-layout .admin-nav {
                display: flex;
                overflow-x: auto;
            }

            .admin-section-layout .admin-nav-link {
                flex: 0 0 auto;
            }
        }
    </style>

    <div class="admin-section-layout">
        @include('admin.partials.sidebar')

        <section class="admin-section-main">
            <header class="mb-4">
                <p class="text-uppercase small fw-bold text-success mb-1">Admin console</p>
                <h1 class="h3 mb-0">{{ $title }}</h1>
                <p class="text-secondary small mt-2 mb-0">{{ $records->total() }} records</p>
                @if ($title === 'Categories')
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm mt-3">
                        <i class="fa-solid fa-plus me-1"></i>Add category
                    </a>
                @endif
                @if ($title === 'Notifications')
                    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-sm mt-3">
                        <i class="fa-solid fa-plus me-1"></i>Create Notification
                    </a>
                @endif
            </header>

            @if (session('status'))
                <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
            @endif

            @if ($title === 'Notifications')
                <div class="mb-3 d-flex flex-wrap gap-1">
                    <a href="{{ route('admin.notifications') }}" class="btn btn-danger text-white btn-noti">All</a>
                    <a href="{{ route('admin.notifications', ['type' => 'general']) }}" class="btn btn-primary text-white btn-noti">General</a>
                    <a href="{{ route('admin.notifications', ['type' => 'maintenance']) }}" class="btn btn-warning text-white btn-noti">Maintenance</a>
                    <a href="{{ route('admin.notifications', ['type' => 'community']) }}" class="btn btn-success text-white btn-noti">Community</a>
                </div>
            @endif

            <div class="admin-section-table-wrap">
                <table class="table admin-section-table align-middle">
                    <thead class="bg-primary text-white">
                        <tr>
                            @foreach ($columns as $label => $key)
                                <th>{{ $label }}</th>
                            @endforeach
                            @if (in_array($title, ['Categories', 'Notifications'], true))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                @foreach ($columns as $key)
                                    @php
                                        $value = data_get($record, $key);
                                    @endphp
                                    @if ($title === 'Notifications' && $key === 'title')
                                        <td><a href="{{ route('admin.notifications.show', $record) }}">{{ $value ?: '-' }}</a></td>
                                    @else
                                    <td>{{ $value instanceof \DateTimeInterface ? $value->format('M d, Y H:i') : (is_string($value) ? \Illuminate\Support\Str::limit($value, 120) : ($value ?? '-')) }}</td>
                                    @endif
                                @endforeach
                                @if ($title === 'Categories')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.categories.edit', $record->id) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="Edit {{ $record->name }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $record->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this category?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete {{ $record->name }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @elseif ($title === 'Notifications')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.notifications.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="Edit notification {{ $record->id }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.notifications.destroy', $record) }}" method="POST"
                                                onsubmit="return confirm('Delete this notification?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete notification {{ $record->id }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + (in_array($title, ['Categories', 'Notifications'], true) ? 1 : 0) }}" class="text-center text-secondary py-4">
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