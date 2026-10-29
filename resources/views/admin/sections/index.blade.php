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

       

        .admin-section-layout .admin-sidebar{
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
                <p class="text-uppercase small fw-bold text-success mb-1">Admin console</p>
                <h1 class="h3 mb-0">{{ $title }}</h1>
                <p class="text-secondary small mt-2 mb-0">{{ $records->total() }} records</p>
                @if ($title === 'Categories')
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm mt-3 mb-3">
                        <i class="fa-solid fa-plus me-1"></i>Add category
                    </a>
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.categories.index') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="category-search">Search category</label>
                        <input id="category-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by category">
                        <button type="submit" aria-label="Search category" title="Search" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="category-sort">Sort by</label>
                        <select id="category-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="category_asc" @selected(request('sort') === 'category_asc')>Category A to Z</option>
                            <option value="category_desc" @selected(request('sort') === 'category_desc')>Category Z to A</option>
                        </select>
                    </div>
                </form>
                @endif
               
                 @if ($title === 'Events')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.events') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="event-search">Search events</label>
                        <input id="event-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by name, title, category or username">
                        <button type="submit" aria-label="Search events" title="Search" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="notification-sort">Sort by</label>
                        <select id="notification-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="event_asc" @selected(request('sort') === 'event_asc')>Name A to Z</option>
                            <option value="event_desc" @selected(request('sort') === 'event_desc')>Name Z to A</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Queue Jobs')
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <form method="POST" action="{{ route('admin.jobs.process-next') }}">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-play me-1"></i>Process next job
                            </button>
                        </form>
                    </div>
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.jobs') }}">
                        <div class="admin-search">
                            <label class="visually-hidden" for="jobs-search">Search jobs</label>
                            <input id="jobs-search" type="search" name="search" value="{{ request('search') }}"
                                placeholder="Search by queue or payload">
                            <button type="submit" aria-label="Search jobs" title="Search" class="btn btn-primary">
                                <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                            </button>
                        </div>
                        <div class="admin-sort">
                            <label for="jobs-sort">Sort by</label>
                            <select id="jobs-sort" name="sort" onchange="this.form.submit()">
                                <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                                <option value="queue_asc" @selected(request('sort') === 'queue_asc')>Queue A to Z</option>
                                <option value="queue_desc" @selected(request('sort') === 'queue_desc')>Queue Z to A</option>
                            </select>
                        </div>
                    </form>
                @endif
                @if ($title === 'Communities')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.communities') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="event-search">Search communities</label>
                        <input id="community-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by name, title, category or username">
                        <button type="submit" aria-label="Search communities" title="Search" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="community-sort">Sort by</label>
                        <select id="community-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="community_asc" @selected(request('sort') === 'community_asc')>Name A to Z</option>
                            <option value="community_desc" @selected(request('sort') === 'community_desc')>Name Z to A</option>
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
                        <label class="visually-hidden" for="notification-search">Search notifications</label>
                        <input id="notification-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by title, message, type or username">
                        <button type="submit" aria-label="Search notifications" title="Search" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="notification-sort">Sort by</label>
                        <select id="notification-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="notification_asc" @selected(request('sort') === 'notification_asc')>Name A to Z</option>
                            <option value="notification_desc" @selected(request('sort') === 'notification_desc')>Name Z to A</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Messages')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.messages') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="message-search">Search messages</label>
                        <input id="message-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by message, name or username">
                        <button type="submit" aria-label="Search messages" title="Search" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="message-sort">Sort by</label>
                        <select id="message-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="message_asc" @selected(request('sort') === 'message_asc')>Name A to Z</option>
                            <option value="message_desc" @selected(request('sort') === 'message_desc')>Name Z to A</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Comments')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.comments') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="comment-search">Search comments</label>
                        <input id="comment-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by comment, event, name or username">
                        <button type="submit" aria-label="Search comments" title="Search" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="comment-sort">Sort by</label>
                        <select id="comment-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="comment_asc" @selected(request('sort') === 'comment_asc')>Name A to Z</option>
                            <option value="comment_desc" @selected(request('sort') === 'comment_desc')>Name Z to A</option>
                        </select>
                    </div>
                </form>
                @endif
                @if ($title === 'Reviews')
                    <form class="admin-toolbar" method="GET" action="{{ route('admin.reviews') }}">
                    <div class="admin-search">
                        <label class="visually-hidden" for="review-search">Search reviews</label>
                        <input id="review-search" type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search by review, event, name or username">
                        <button type="submit" aria-label="Search reviews" title="Search" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                        </button>
                    </div>
                    <div class="admin-sort">
                        <label for="review-sort">Sort by</label>
                        <select id="review-sort" name="sort" onchange="this.form.submit()">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                            <option value="review_asc" @selected(request('sort') === 'review_asc')>Name A to Z</option>
                            <option value="review_desc" @selected(request('sort') === 'review_desc')>Name Z to A</option>
                        </select>
                    </div>
                </form>
                @endif
            </header>

            @if (session('status'))
                <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
            @endif

            @if ($title === 'Notifications')
            <div class="mb-3 d-flex flex-wrap">
                    <a href="{{ route('admin.notifications.create') }}" class="btn btn-white border-primary rounded text-primary btn-noti btn-outline-primary btn-custom-outline">Create Announce</a>
            </div>
                <div class="mb-3 d-flex flex-wrap gap-1">
                    <a href="{{ route('admin.notifications') }}" class="btn btn-white border-danger rounded text-danger btn-noti btn-outline-danger btn-custom-outline">All</a>
                    <a href="{{ route('admin.notifications', ['type' => 'general']) }}" class="btn btn-white text-secondary rounded border-secondary btn-noti btn-outline-secondary btn-custom-outline" >General</a>
                    <a href="{{ route('admin.notifications', ['type' => 'maintenance']) }}" class="btn btn-white border-warning rounded text-warning btn-noti btn-outline-warning btn-custom-outline" >Maintenance</a>
                    <a href="{{ route('admin.notifications', ['type' => 'community']) }}" class="btn btn-white border-success rounded text-success btn-noti btn-outline-success btn-custom-outline">Community</a>
                </div>
            @endif

            <div class="admin-section-table-wrap">
                <table class="table admin-section-table align-middle table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            @foreach ($columns as $label => $key)
                                <th>{{ $label }}</th>
                            @endforeach
                            @if (in_array($title, ['Categories', 'Events', 'Notifications', 'Messages', 'Communities', 'Comments', 'Reviews', 'Queue Jobs'], true))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($records as $record)
                                @php
                                    $showUrl = match ($title) {
                                        'Notifications' => route('admin.notifications.show', $record),
                                        'Events' => route('admin.events.show', $record),
                                        'Communities' => route('admin.communities.show', $record),
                                        'Messages' => route('admin.messages.show', $record),
                                        'Contacts' => route('admin.contacts.show', $record),
                                        'Comments' => route('admin.comments.show', $record),
                                        'Reviews' => route('admin.reviews.show', $record),
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
                                        @elseif ($title === 'Messages' && $key === 'message')
                                            <td>{{ $value ? \Illuminate\Support\Str::limit($value, 120) : '-' }}</td>
                                        @elseif ($title === 'Contacts' && $key === 'detail')
                                            <td>{{ $value ? \Illuminate\Support\Str::limit($value, 120) : '-' }}</td>
                                        @else
                                            <td>{{ $value instanceof \DateTimeInterface ? $value->format('M d, Y H:i') : (is_bool($value) ? ($value ? 'Yes' : 'No') : (is_string($value) ? (\Illuminate\Support\Str::limit($value, 120) ?: '-') : ($value ?? '-'))) }}</td>
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
                                                data-confirm="true" data-confirm-message="Delete this category?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete {{ $record->name }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                 @elseif ($title === 'Events')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.events.show', $record) }}"
                                                class="btn btn-outline-secondary btn-sm" aria-label="View event {{ $record->title }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.events.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="Edit event {{ $record->title }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.events.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-message="Delete this event?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete event {{ $record->title }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                     @elseif ($title === 'Communities')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.communities.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="Edit community {{ $record->name }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.communities.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-message="Delete this community?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete community {{ $record->name }}">
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
                                                data-confirm="true" data-confirm-message="Delete this notification?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete notification {{ $record->id }}">
                                                    <i class="fa-solid fa-trash"></i>
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
                                                    <button type="submit" class="btn btn-outline-success btn-sm"><i class="fa-regular fa-square-plus"></i></button>
                                                </form>
                                            @elseif ($record->sender && (int) $record->sender->id !== (int) auth()->id())
                                                <form action="{{ route('admin.messages.block', $record->sender) }}" method="POST"
                                                    data-confirm="true" data-confirm-message="Block this sender?">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-ban"></i></button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.messages.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-message="Delete this message?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                  @elseif ($title === 'Comments')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.comments.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="Edit comment {{ $record->id }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.comments.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-message="Delete this comment?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete comment {{ $record->id }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                      @elseif ($title === 'Reviews')
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.reviews.edit', $record) }}"
                                                class="btn btn-outline-primary btn-sm" aria-label="Edit review {{ $record->id }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.reviews.destroy', $record) }}" method="POST"
                                                data-confirm="true" data-confirm-message="Delete this review?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                                    aria-label="Delete review {{ $record->id }}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    @elseif ($title === 'Queue Jobs')
                                    <td>
                                        {{-- <form action="{{ route('admin.jobs.destroy', $record->id) }}" method="POST"
                                            data-confirm="true" data-confirm-message="Delete this queued job?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                                aria-label="Delete queued job {{ $record->id }}" title="Delete queued job">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>--}}
                                    </td>
                                @endif 
                               
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) + (in_array($title, ['Categories', 'Events', 'Notifications', 'Messages', 'Communities', 'Comments', 'Reviews', 'Queue Jobs'], true) ? 1 : 0) }}" class="text-center text-secondary py-4">
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