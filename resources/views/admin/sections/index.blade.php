@extends('layouts.app')

@section('title', __('Admin') . ' ' . __($title))

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

       

        .admin-section-layout .admin-sidebar{
            position: fixed;
            inset-inline-start: 0;
            top: 74px;
            width: 210px;
            height: calc(100vh - 74px);
            overflow-y: auto;
            z-index: 100;
            padding: 24px 14px;
            border-inline-end: 1px solid var(--admin-line);
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
        .admin-section-layout .admin-nav-link.active{
            color: #126b6b;
            background: #d9f2ef;
        }

        .table-hover tbody tr:hover td, .table-hover tbody tr:hover th {
            color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        

        .admin-section-layout .admin-nav-link.active {
            font-weight: 700;
        }

        .admin-section-main {
            grid-column: 2;
            min-width: 0;
            padding: 28px clamp(16px, 3vw, 42px) 36px;
        }

        footer {
            position: relative;
            z-index: 101;
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


        .admin-section-table tbody td {
            background-color: transparent !important;
        }

        .btn-noti{
            width:150px;
        }

        
        .admin-search {
            display: flex;
            flex: 1 1 340px;
            max-width: 540px;
        }

        .admin-search input,
        .admin-sort select {
            min-height: 40px;
            border: 1px solid #cbdedb;
            color: var(--admin-ink);
            background: #fff;
        }

        .admin-search input {
            min-width: 0;
            flex: 1;
            padding: 0 12px;
            border-radius: 4px 0 0 4px;
        }

        .admin-search button {
            width: 44px;
            border: 0;
            border-radius: 0 4px 4px 0;
            color: #fff;
            /* background: #238f91; */
        }

        .admin-sort {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--admin-muted);
            font-size: 13px;
        }

        .admin-sort select {
            min-width: 150px;
            padding: 0 30px 0 10px;
            border-radius: 4px;
        }

        .admin-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
            padding: 12px;
            border: 1px solid var(--admin-line);
            border-radius: 6px;
            background: #fff;
        }

        .btn-custom-outline:hover,
        .btn-custom-outline:active {
            color: #ffffff !important;
        }

        @media (max-width: 760px) {
            .admin-section-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .admin-section-layout .admin-sidebar {
                position: static;
                height: auto;
                overflow: visible;
                position: static;
                width: auto;
                height: auto;
                overflow: visible;
                padding: 10px 12px;
                border-inline-end: 0;
                border-block-end: 1px solid var(--admin-line);
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

            .admin-section-main {
                grid-column: 1;
            }

             .admin-search {
                max-width: none;
                flex-basis: auto;
            }

            .admin-sort {
                justify-content: space-between;
            }
        }
    </style>

    
    <div class="admin-section-layout">
        @include('admin.partials.sidebar')

        <section class="admin-section-main">
            <header class="mb-4">
                <p class="text-uppercase small fw-bold text-success mb-1">{{ __('Admin console') }}</p>
                <h1 class="h3 mb-0">{{ __($title) }}</h1>
                <p class="text-secondary small mt-2 mb-0">{{ __('records', ['count' => $records->total()]) }}</p>
                @if ($title === 'Categories')
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm mt-3 mb-3">
                        <i class="fa-solid fa-plus me-1"></i>{{ __('Add category') }}
                    </a>
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.categories.index') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="category-search">{{ __('Search category') }}</label>
                        <input id="category-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search by category') }}">
                        <button type="submit" aria-label="{{ __('Search category') }}" title="{{ __('Search') }}" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="category-sort">{{ __('Sort by') }}</label>
                        <select id="category-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                            <option value="category_asc" @selected(request('sort') === 'category_asc')>{{ __('Category A to Z') }}</option>
                            <option value="category_desc" @selected(request('sort') === 'category_desc')>{{ __('Category Z to A') }}</option>
                        </select>
                    </div>
                </form>
                @endif
               
                 @if ($title === 'Events')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.events') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="event-search">{{ __('Search events') }}</label>
                        <input id="event-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search by name, title, category or username') }}">
                        <button type="submit" aria-label="{{ __('Search events') }}" title="{{ __('Search') }}" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="notification-sort">{{ __('Sort by') }}</label>
                        <select id="notification-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                            <option value="event_asc" @selected(request('sort') === 'event_asc')>{{ __('Name A to Z') }}</option>
                            <option value="event_desc" @selected(request('sort') === 'event_desc')>{{ __('Name Z to A') }}</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Queue Jobs')
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <form method="POST" action="{{ route('admin.jobs.process-next') }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-play me-1"></i>{{ __('Process next job') }}
                            </button>
                        </form>
                    </div>
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.jobs') }}">
                        <div class="admin-search">
                            <label class="visually-hidden" for="jobs-search">{{ __('Search jobs') }}</label>
                            <input id="jobs-search" type="search" name="search" value="{{ request('search') }}"
                                placeholder="{{ __('Search by queue or payload') }}">
                            <button type="submit" aria-label="{{ __('Search jobs') }}" title="{{ __('Search') }}" class="btn btn-primary">
                                <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                            </button>
                        </div>
                        <div class="admin-sort">
                            <label for="jobs-sort">{{ __('Sort by') }}</label>
                            <select id="jobs-sort" name="sort" onchange="this.form.submit()">
                                <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                                <option value="queue_asc" @selected(request('sort') === 'queue_asc')>{{ __('Queue A to Z') }}</option>
                                <option value="queue_desc" @selected(request('sort') === 'queue_desc')>{{ __('Queue Z to A') }}</option>
                            </select>
                        </div>
                    </form>
                @endif
                @if ($title === 'Communities')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.communities') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="event-search">{{ __('Search communities') }}</label>
                        <input id="community-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search by name, title, category or username') }}">
                        <button type="submit" aria-label="{{ __('Search communities') }}" title="{{ __('Search') }}" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="community-sort">{{ __('Sort by') }}</label>
                        <select id="community-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                            <option value="community_asc" @selected(request('sort') === 'community_asc')>{{ __('Name A to Z') }}</option>
                            <option value="community_desc" @selected(request('sort') === 'community_desc')>{{ __('Name Z to A') }}</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Notifications')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.notifications') }}">
                    @if (request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="admin-search">
                        <label class="visually-hidden" for="notification-search">{{ __('Search notifications') }}</label>
                        <input id="notification-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search by title, message, type or username') }}">
                        <button type="submit" aria-label="{{ __('Search notifications') }}" title="{{ __('Search') }}" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="notification-sort">{{ __('Sort by') }}</label>
                        <select id="notification-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                            <option value="notification_asc" @selected(request('sort') === 'notification_asc')>{{ __('Name A to Z') }}</option>
                            <option value="notification_desc" @selected(request('sort') === 'notification_desc')>{{ __('Name Z to A') }}</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Messages')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.messages') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="message-search">{{ __('Search messages') }}</label>
                        <input id="message-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search by message, name or username') }}">
                        <button type="submit" aria-label="{{ __('Search messages') }}" title="{{ __('Search') }}" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="message-sort">{{ __('Sort by') }}</label>
                        <select id="message-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                            <option value="message_asc" @selected(request('sort') === 'message_asc')>{{ __('Name A to Z') }}</option>
                            <option value="message_desc" @selected(request('sort') === 'message_desc')>{{ __('Name Z to A') }}</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Comments')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.comments') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="comment-search">{{ __('Search comments') }}</label>
                        <input id="comment-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search by comment, event, name or username') }}">
                        <button type="submit" aria-label="{{ __('Search comments') }}" title="{{ __('Search') }}" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="comment-sort">{{ __('Sort by') }}</label>
                        <select id="comment-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                            <option value="comment_asc" @selected(request('sort') === 'comment_asc')>{{ __('Name A to Z') }}</option>
                            <option value="comment_desc" @selected(request('sort') === 'comment_desc')>{{ __('Name Z to A') }}</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Reviews')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.reviews') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="review-search">{{ __('Search reviews') }}</label>
                        <input id="review-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="{{ __('Search by review, event, name or username') }}">
                        <button type="submit" aria-label="{{ __('Search reviews') }}" title="{{ __('Search') }}" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="review-sort">{{ __('Sort by') }}</label>
                        <select id="review-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('Oldest') }}</option>
                            <option value="review_asc" @selected(request('sort') === 'review_asc')>{{ __('Name A to Z') }}</option>
                            <option value="review_desc" @selected(request('sort') === 'review_desc')>{{ __('Name Z to A') }}</option>
                        </select>
                    </div>
                </form>
                @endif
            </header>

            @if (session('status'))
                <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
            @endif

            @if (request()->routeIs('admin.notifications') || request()->routeIs('admin.notifications.*'))
            <div class="mb-3 d-flex flex-wrap">
                    <a href="{{ route('admin.notifications.create') }}" class="btn btn-white border-primary rounded text-primary btn-noti btn-outline-primary btn-custom-outline">{{ __('Create Announce') }}</a>
            </div>
                <div class="mb-3 d-flex flex-wrap gap-1">
                    <a href="{{ route('admin.notifications') }}" class="btn btn-white border-danger rounded text-danger btn-noti btn-outline-danger btn-custom-outline">{{ __('All') }}</a>
                    <a href="{{ route('admin.notifications', ['type' => 'general']) }}" class="btn btn-white text-secondary rounded border-secondary btn-noti btn-outline-secondary btn-custom-outline" >{{ __('General') }}</a>
                    <a href="{{ route('admin.notifications', ['type' => 'maintenance']) }}" class="btn btn-white border-warning rounded text-warning btn-noti btn-outline-warning btn-custom-outline" >{{ __('Maintenance') }}</a>
                    <a href="{{ route('admin.notifications', ['type' => 'community']) }}" class="btn btn-white border-success rounded text-success btn-noti btn-outline-success btn-custom-outline">{{ __('Community') }}</a>
                </div>
            @endif

            <div class="admin-section-table-wrap">
                <table class="table admin-section-table align-middle table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            @foreach ($columns as $label => $key)
                                <th>{{ __($label) }}</th>
                            @endforeach
                            @if (in_array($title, ['Categories', 'Events', 'Notifications', 'Messages', 'Communities', 'Comments', 'Reviews', 'Queue Jobs', 'Read at', 'Sent'], true))
                                <th>{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($records as $record)
                                @php
                                    $showUrl = match ($title) {
                                        'Events' => route('admin.events.show', $record),
                                        'Communities' => route('admin.communities.show', $record),
                                        'Messages' => route('admin.messages.show', $record),
                                        'Contacts' => route('admin.contacts.show', $record),
                                        'Comments' => route('admin.comments.show', $record),
                                        'Reviews' => route('admin.reviews.show', $record),
                                        'Notifications' => ($record->type === 'community_message' && $record->related_id
                                            ? route('community.show', $record->related_id).'#chat'
                                            : (in_array($record->type, ['community_join_approved', 'community_join_declined', 'community_approval'], true) && $record->related_id
                                                ? route('community.show', $record->related_id)
                                                : route('admin.notifications.show', $record))),
                                        default => null,
                                    };
                                @endphp
                                <tr @if($showUrl) style="cursor: pointer;" onclick="if(event.target.closest('a, button, form, input')) return; window.location.href='{{ $showUrl }}';" @endif>
                                    
                                    @foreach ($columns as $key)
                                        @php
                                            $value = data_get($record, $key);
                                        @endphp

                                        @if (in_array($title, ['Comments', 'Reviews'], true) && $key === 'event.title')
                                            <td>{{ $record->event ? $value : '-' }}</td>
                                        @elseif ($title === 'Categories' && $key === 'name')
                                            <td>{{ \App\Support\LocalizedValue::category($value) }}</td>
                                        @elseif ($title === 'Categories' && $key === 'localized_name')
                                            <td>{{ $value ?: '-' }}</td>
                                        @elseif ($title === 'Notifications' && $key === 'title')
                                            <td>
                                                @php
                                                    $notificationUrl = $record->type === 'community_message' && $record->related_id
                                                        ? route('community.show', $record->related_id).'#chat'
                                                        : (in_array($record->type, ['community_join_approved', 'community_join_declined', 'community_approval'], true) && $record->related_id
                                                            ? route('community.show', $record->related_id)
                                                            : route('admin.notifications.show', $record));
                                                @endphp
                                                <a href="{{ $notificationUrl }}" class="text-decoration-none text-dark">{{ $value ?: '-' }}</a>
                                            </td>
                                        @elseif ($title === 'Messages' && $key === 'message')
                                            
                                            <td>{{ $value ? \Illuminate\Support\Str::limit($value, 120) : '-' }}</td>
                                        @elseif ($title === 'Contacts' && $key === 'detail')
                                            <td>{{ $value ? \Illuminate\Support\Str::limit($value, 120) : '-' }}</td>
                                        @elseif ($key === 'type')
                                            <td>{{ \App\Support\LocalizedValue::notificationType($value) }}</td>
                                        @elseif ($key === 'approval_required')
                                            <td>{{ \App\Support\LocalizedValue::approval((bool) $value) }}</td>
                                        @elseif ($key === 'status')
                                            <td>{{ \App\Support\LocalizedValue::status($value) }}</td>
                                        @elseif ($key === 'role')
                                            <td>{{ \App\Support\LocalizedValue::role($value) }}</td>
                                        @elseif ($key === 'visibility')
                                            <td>{{ \App\Support\LocalizedValue::visibility($value) }}</td>
                                        @elseif ($key === 'language')
                                            <td>{{ \App\Support\LocalizedValue::language($value) }}</td>
                                        @elseif ($key === 'preferred_language')
                                            <td>{{ \App\Support\LocalizedValue::language($value) }}</td>
                                        @elseif ($key === 'rating')
                                            <td>{{ \App\Support\LocalizedValue::reviewRating((int) $value) }}</td>
                                        @elseif ($key === 'category_names')
                                            <td>{{ $value ?: '-' }}</td>
                                        @else
                                            <td>{{ $value instanceof \DateTimeInterface ? $value->format('M d, Y H:i') : (is_bool($value) ? ($value ? __('Yes') : __('No')) : (is_string($value) ? (\Illuminate\Support\Str::limit($value, 120) ?: '-') : ($value ?? '-'))) }}</td>
                                        @endif
                                    @endforeach
                                                @if ($title === 'Categories')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.categories.edit', $record->id) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="{{ __('Edit :name', ['name' => $record->name]) }}" title="{{ __('Edit') }}">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $record->id) }}" method="POST"
                                                data-confirm="true" data-confirm-action="{{ __('Delete') }}" data-confirm-message="{{ __('Delete this category?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="{{ __('Delete :name', ['name' => $record->name]) }}" title="{{ __('Delete') }}">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                 @elseif ($title === 'Events')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.events.show', $record) }}"
                                                class="btn btn-outline-secondary btn-sm" aria-label="{{ __('View event :title', ['title' => $record->title]) }}" title="{{ __('View') }}">
                                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                            </a>
                                            <a href="{{ route('admin.events.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="{{ __('Edit event :title', ['title' => $record->title]) }}" title="{{ __('Edit') }}">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('admin.events.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-action="{{ __('Delete') }}" data-confirm-message="{{ __('Delete this event?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="{{ __('Delete event :title', ['title' => $record->title]) }}" title="{{ __('Delete') }}">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                     @elseif ($title === 'Communities')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.communities.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="{{ __('Edit community :name', ['name' => $record->name]) }}" title="{{ __('Edit') }}">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('admin.communities.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-action="{{ __('Delete') }}" data-confirm-message="{{ __('Delete this community?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="{{ __('Delete community :name', ['name' => $record->name]) }}" title="{{ __('Delete') }}">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @elseif ($title === 'Notifications')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.notifications.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="{{ __('Edit notification :id', ['id' => $record->id]) }}" title="{{ __('Edit') }}">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('admin.notifications.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-action="{{ __('Delete') }}" data-confirm-message="{{ __('Delete this notification?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="{{ __('Delete notification :id', ['id' => $record->id]) }}" title="{{ __('Delete') }}">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                   @elseif ($title === 'Messages')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($record->sender && in_array((int) $record->sender->id, $blockedUserIds ?? [], true))
                                                <form action="{{ route('admin.messages.unblock', $record->sender) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-success btn-sm" title="{{ __('Unblock') }}">
                                                        <i class="fa-regular fa-square-plus" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            @elseif ($record->sender && (int) $record->sender->id !== (int) auth()->id())
                                                <form action="{{ route('admin.messages.block', $record->sender) }}" method="POST"
                                                    data-confirm="true" data-confirm-message="{{ __('Block this sender?') }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm" title="{{ __('Block') }}">
                                                        <i class="fa-solid fa-ban" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.messages.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-action="{{ __('Delete') }}" data-confirm-message="{{ __('Delete this message?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('Delete') }}">
                                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                  @elseif ($title === 'Comments')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.comments.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="{{ __('Edit comment :id', ['id' => $record->id]) }}" title="{{ __('Edit') }}">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('admin.comments.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-action="{{ __('Delete') }}" data-confirm-message="{{ __('Delete this comment?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="{{ __('Delete comment :id', ['id' => $record->id]) }}" title="{{ __('Delete') }}">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                      @elseif ($title === 'Reviews')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.reviews.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="{{ __('Edit review :id', ['id' => $record->id]) }}" title="{{ __('Edit') }}">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('admin.reviews.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-action="{{ __('Delete') }}" data-confirm-message="{{ __('Delete this review?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="{{ __('Delete review :id', ['id' => $record->id]) }}" title="{{ __('Delete') }}">
                                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @elseif ($title === 'Queue Jobs')
                                    <td>
                                        {{-- <form action="{{ route('admin.jobs.destroy', $record->id) }}" method="POST"
                                            data-confirm="true" data-confirm-message="{{ __('Delete this queued job?') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                aria-label="{{ __('Delete queued job :id', ['id' => $record->id]) }}" title="{{ __('Delete queued job') }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>--}}
                                    </td>
                                @endif 
                               
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) + (in_array($title, ['Categories', 'Events', 'Notifications', 'Messages', 'Communities', 'Comments', 'Reviews', 'Queue Jobs'], true) ? 1 : 0) }}" class="text-center text-secondary py-4">
                                        {{ __('No :title to display.', ['title' => mb_strtolower(__($title))]) }}
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