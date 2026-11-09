{{-- ホームとイベント／コミュニティで共通のヘッダー --}}
    <nav class="navbar navbar-expand-md navbar-light bg-white text-dark">
        <div class="container-fluid px-2">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/onlylogo.jpg') }}" alt="Sana All Community" style="height: 45px;"
                    class="">
            </a>
            <p class="h5"><strong class="text-bold">Sana ALL</strong><br>Community</p>

            {{-- <div class="d-flex align-items-center flex-shrink-0">
                    <i class="fa-solid fa-map-pin text-white"></i>
                    <p class="text-white ms-1 location text-center mb-0">Deliver to <br>Philippines</p>
                </div> --}}

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">

                    <!-- CATEGORIES -->
                    <li class="nav-item d-flex align-items-center category-nav">
                        <a class="nav-link text-dark" href="{{ route('events.index') }}">EVENTS</a>
                        <a class="nav-link text-dark" href="#">EXCHANGE</a>
                        <a class="nav-link text-dark" href="#">MEET UP</a>
                        <a class="nav-link text-dark" href="#">JOB</a>
                        <a class="nav-link text-dark" href="#">OTHER</a>
                        <a class="nav-link {{ ($inCommunity ?? request()->routeIs('community.*')) ? 'community-nav-active' : 'text-dark' }}" href="{{ route('community.index')}}">COMMUNITY</a>
                        <a class="nav-link text-dark" href="#">VIEW ALL</a>
                    </li>

                    <ul class="dropdown-menu" aria-labelledby="categoryDropdown">

                        <li>
                            <a class="dropdown-item"href="#">
                                japanese
                            </a>
                            <a class="dropdown-item"href="#">
                                english
                            </a>
                        </li>
                    </ul>

                        <li class="nav-item dropdown">
                            <button id="languageDropdown" class="nav-link dropdown-toggle text-dark" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-language me-1" aria-hidden="true"></i>
                                {{ app()->getLocale() === 'ja' ? '日本語' : 'English' }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <li><a class="dropdown-item" href="#">English</a></li>
                                <li><a class="dropdown-item" href="#">日本語</a></li>
                            </ul>
                        </li>

                            <ul>
                                {{-- show messages --}}
                                <li class="nav-item dropdown d-flex align-items-center category-nav">
                                    <button id="messageDropdown" class="nav-link dropdown-toggle text-dark no-arrow" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-regular fa-envelope dropdown-icon-lg"></i>
                                        <span id="unread-messages-badge" class="badge rounded-pill bg-danger"
                                            @if ($unreadMessagesCount < 1) hidden @endif>{{ $unreadMessagesCount }}</span>
                                    </button>
                                    {{-- show latest messages --}}
                                    <ul class="dropdown-menu border-primary rounded" aria-labelledby="messageDropdown"
                                        style="max-height: 24rem; min-width: 18rem; overflow-y: auto;">
                                        <li class="bg-primary text-center"><span class="h5 text-white">Messages</span></li>
                                        @forelse ($chats as $chat)
                                            <li class="dropdown-content">
                                                <button type="button"
                                                    class="dropdown-item text-wrap border-bottom text-start js-open-message-chat"
                                                    data-message-chat-url="{{ route('messages.conversation', $chat['participant']->id) }}"
                                                    data-message-chat-send-url="{{ route('messages.send', $chat['participant']->id) }}"
                                                    data-message-chat-name="{{ $chat['participant']->username ?? $chat['participant']->name ?? __('Unknown user') }}">
                                                    <div class="d-flex justify-content-between align-items-baseline gap-2">
                                                        <span class="admin-avatar">
                                                            @if ($chat['participant']->profile?->avatar)
                                                                <img src="{{ $chat['participant']->profile->avatar }}" alt="{{ $chat['participant']->name }}">
                                                            @else
                                                                {{ strtoupper(substr($chat['participant']->name ?? '?', 0, 1)) }}
                                                            @endif
                                                        </span>
                                                        <strong>{{ $chat['participant']->username ?? $chat['participant']->name ?? __('Unknown user') }}</strong>
                                                        @if ($chat['last_message']->created_at)
                                                            <time class="small text-muted text-nowrap" datetime="{{ $chat['last_message']->created_at->toIso8601String() }}">
                                                                {{ $chat['last_message']->created_at->format('M j, Y g:i A') }}
                                                            </time>
                                                        @else
                                                            <span class="small text-muted text-nowrap">{{ __('Time unavailable') }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-break">{{ $chat['last_message']->message }}</div>
                                                </button>
                                            </li>
                                        @empty
                                            <li><span class="dropdown-item-text text-muted">{{ __('No messages') }}</span></li>
                                        @endforelse
                                        {{-- go to the all show messages --}}
                                        <li><a href="{{ route('messages.index') }}" class="dropdown-item text-primary">Show All</a></li>
                                            <li><button type="button" class="dropdown-item text-primary" data-bs-toggle="modal"
                                                data-bs-target="#create-message-modal">Create Message</button></li>
                                    </ul>
                                </li>
                            </ul>
                            {{-- show the latest notifications --}}
                            <ul>
                                <li class="nav-item dropdown d-flex align-items-center category-nav">
                                    <button id="notificationDropdown" class="nav-link dropdown-toggle text-dark no-arrow" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Notifications') }}">
                                        <i class="fa-regular fa-bell dropdown-icon-lg" aria-hidden="true"></i>
                                        @if ($unreadNotificationCount > 0)
                                            <span class="badge rounded-pill bg-danger">{{ $unreadNotificationCount }}</span>
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu border-primary rounded" aria-labelledby="notificationDropdown"
                                        style="max-height: 24rem; min-width: 18rem; overflow-y: auto;">
                                        <li class="text-center bg-success"><span class="h5 text-white">Notifications</span></li>
                                        @forelse ($notifications as $notification)
                                            <li class="dropdown-content">
                                                <a href="{{ $notification->type === 'community_message' && $notification->related_id
                                                    ? route('community.show', $notification->related_id) . '#chat'
                                                    : route('notifications.show', $notification) }}"
                                                    class="dropdown-item text-wrap border-bottom text-start {{ $notification->read_at ? '' : 'fw-semibold bg-body-tertiary' }}">
                                                        @if ($notification->type === 'community_message')
                                                            <span class="d-block">{{ $notification->related?->name ?? __('Community') }}</span>
                                                        @else
                                                            <span class="d-block">{{ $notification->title ?: ucfirst(str_replace('_', ' ', $notification->type)) }}</span>
                                                            @if ($notification->sender_name)
                                                                <span class="d-block small text-muted">{{ __('From') }}: {{ $notification->sender_name }}</span>
                                                            @endif
                                                            @if ($notification->message)
                                                                <span class="d-block small text-secondary">{{ \Illuminate\Support\Str::limit($notification->message, 100) }}</span>
                                                            @endif
                                                        @endif
                                                        @if ($notification->created_at)
                                                            <time class="d-block small text-muted" datetime="{{ $notification->created_at->toIso8601String() }}">
                                                                {{ $notification->created_at->format('M j, Y g:i A') }}
                                                            </time>
                                                        @endif
                                                </a>
                                            </li>
                                        @empty
                                            <li><span class="dropdown-item-text text-muted">{{ __('No notifications') }}</span></li>
                                        @endforelse
                                        <li><a href="{{ route('notifications.index') }}" class="dropdown-item text-primary">{{ __('Show All') }}</a></li>
                                    </ul>
                                </li>
                            </ul>
                            <ul>
                                <li class="nav-item dropdown d-flex align-items-center category-nav">
                                    <a class="nav-link text-dark link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-calendar" href="#"> <i class="fa-regular fa-calendar dropdown-icon-lg"></i></a>
                                </li>
                            </ul>
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Create Account') }}</a>
                                </li>
                            @endif
                        @else
                            <a href="#" class="btn btn-primary text-white btn-create mt-2 d-inline-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modal-post">
                                <span>CREATE</span>
                            </a>
                            <li class="nav-item dropdown">

                                <a id="navbarDropdown" class="nav-link dropdown-toggle text-dark" href="#"
                                    role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    v-pre>
                                     @if (Auth::user()->profile?->avatar)
                                        @php
                                            $avatarPath = Auth::user()->profile->avatar;
                                            $avatarUrl = str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                                ? $avatarPath : asset('storage/'.$avatarPath);
                                        @endphp
                                        <img src="{{ $avatarUrl }}" alt="{{ Auth::user()->username }}" class="rounded-circle navbar-avatar" width="36" height="36">
                                    @else
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    @endif
                                    Hello, {{ Auth::user()->username }}
                                    <i class="fa-solid fa-list"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    @if (Auth::user()->role === 'admin')
                                        <a href="{{ Route::has('admin.users') ? route('admin.users') : '#' }}"
                                            class="dropdown-item">
                                            <i class="fa-solid fa-user-gear"></i> Admin
                                        </a>
                                    @endif

                                    <a href="{{ route('profile.show', Auth::user()->id) }}" class="dropdown-item">
                                        <i class="fa-solid fa-circle-user user-menu-icon"></i> Profile
                                    </a>

                                     <a href="#" class="dropdown-item">
                                        <i class="fa-solid fa-book-bookmark user-menu-icon"></i> Bookmark
                                    </a>

                                     <a href="#" class="dropdown-item">
                                        <i class="fa-solid fa-person-walking user-menu-icon"></i> Activity
                                    </a>

                                     <a href="#" class="dropdown-item">
                                        <i class="fa-solid fa-people-group user-menu-icon"></i> Community
                                    </a>

                                     <a href="{{ route('notifications.index') }}" class="dropdown-item">
                                        <i class="fa-regular fa-message user-menu-icon"></i>
                                        Message
                                     </a>
                                    <hr class="dropdown-divider">

                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                             document.getElementById('logout-form').submit();">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
