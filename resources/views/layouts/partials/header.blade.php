{{-- ホームとイベント／コミュニティで共通のヘッダー --}}
        <nav class="navbar navbar-expand-md navbar-light bg-white text-dark">
            <div class="container-fluid px-2">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                    <img src="{{ asset('images/onlylogo.jpg') }}" alt="Sana All Community" style="height: 45px;">
                    <div class="h5 mb-0 lh-sm">
                        <strong class="fw-bold">Sana ALL</strong><br>
                        <small class="text-muted">Community</small>
                    </div>
                </a>
                

                {{-- <div class="d-flex align-items-center flex-shrink-0">
                        <i class="fa-solid fa-map-pin text-white"></i>
                        <p class="text-white ms-1 location text-center mb-0">Deliver to <br>Philippines</p>
                    </div> --}}

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="my-auto">
                    <div class="collapse navbar-collapse all-navbar" id="navbarSupportedContent">
                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto my-auto">

                            <!-- CATEGORIES -->
                            <li class="nav-item category-nav">
                                {{-- <button id="categoryDropdown" class="nav-link dropdown-toggle text-dark" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Categories
                                </button> --}}
                                <ul class="list-unstyled d-flex flex-wrap align-items-center mb-0 mt-3">
                                    {{-- <li>
                                        <a class="dropdown-item" href="{{ route('events.index') }}">All events</a>
                                    </li> --}}
                                    @isset($categories)
                                        @foreach ($categories as $value => $label)
                                            <li class="me-3 h5 fw-bold header-list">
                                                <a class="dropdown-item {{ (string) request('category') === (string) $value ? 'active' : '' }}"
                                                    href="{{ route('events.index', ['category' => $value]) }}">
                                                    {{ $label }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endisset
                                </ul>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-dark header-list" href="{{ route('community.index', ['type' => 'community']) }}"><span class="h5 fw-normal">Community</span></a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-dark header-list" href="{{ route('events.index') }}"><span class="h5 fw-normal">View All</span></a>
                            </li>

                                <li class="nav-item dropdown my-auto ms-1 mt-1">
                                    <button id="languageDropdown" class="nav-link dropdown-toggle text-dark fs-5" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-language me-1" aria-hidden="true"></i>
                                        {{ app()->getLocale() === 'ja' ? '日本語' : 'English' }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                        <li><a class="dropdown-item" href="#">English</a></li>
                                        <li><a class="dropdown-item" href="#">日本語</a></li>
                                    </ul>
                                </li>
                                   
                                    <ul class="navbar-icon-group">
                                        {{-- show messages --}}
                                        <li class="nav-item dropdown d-flex align-items-center category-nav mt-2">
                                            @guest
                                            <li class="nav-item">
                                                <a class="nav-link btn text-dark" href="{{ route('login') }}"><i class="fa-regular fa-envelope dropdown-icon-lg"></i></a>
                                            </li>
                                            @else
                                        <button id="messageDropdown" class="nav-link dropdown-toggle text-dark no-arrow position-relative" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-regular fa-envelope dropdown-icon-lg"></i>
                                            <span id="unread-messages-badge" class="badge rounded-pill bg-danger position-absolute bottom-0 end-0"
                                                    @if ($unreadMessagesCount < 1) hidden @endif>{{ $unreadMessagesCount }}</span>
                                            </button>
                                            {{-- show latest messages --}}
                                            <ul class="dropdown-menu border-primary bg-primary rounded" aria-labelledby="messageDropdown"
                                                style="max-height: 24rem; min-width: 18rem; overflow-y: auto;">
                                                <li class="bg-primary text-center mb-1"><span class="h5 text-white">Messages</span></li>
                                                @forelse ($chats as $chat)
                                                    <li class="dropdown-content bg-white">
                                                        <button type="button"
                                                            class="dropdown-item border-bottom text-start js-open-message-chat {{ $chat['has_unread'] ? 'dropdown-item--unread fw-semibold' : '' }}"
                                                            data-message-chat-url="{{ route('messages.conversation', $chat['participant']->id) }}"
                                                            data-message-chat-send-url="{{ route('messages.send', $chat['participant']->id) }}"
                                                            data-message-chat-name="{{ $chat['participant']->username ?? $chat['participant']->name ?? __('Unknown user') }}">
                                                            <div class="d-flex w-100 flex-nowrap align-items-baseline gap-2">
                                                                <span class="admin-avatar flex-shrink-0">
                                                                    @if ($chat['participant']->profile?->avatar)
                                                                         @php
                                                                            $avatarPath = $chat['participant']->profile->avatar;
                                                                            $avatarUrl = str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                                                                ? $avatarPath : asset('storage/'.$avatarPath);
                                                                        @endphp
                                                                        <img src="{{ $avatarUrl }}" alt="{{ $chat['participant']->username }}" class="rounded-circle navbar-avatar" width="10" height="10">
                                                                    @else
                                                                        {{ strtoupper(substr($chat['participant']->name ?? '?', 0, 1)) }}
                                                                    @endif
                                                                </span>
                                                                <strong class="flex-grow-1 text-truncate" style="min-width: 0;">
                                                                    {{ $chat['participant']->username ?? $chat['participant']->name ?? __('Unknown user') }}
                                                                </strong>
                                                                @if ($chat['last_message']->created_at)
                                                                    <time class="small text-muted text-nowrap flex-shrink-0" datetime="{{ $chat['last_message']->created_at->toIso8601String() }}">
                                                                        {{ $chat['last_message']->created_at->format('M j, Y g:i A') }}
                                                                    </time>
                                                                @else
                                                                    <span class="small text-muted text-nowrap flex-shrink-0">{{ __('Time unavailable') }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-break">{{ $chat['last_message']->message }}</div>
                                                        </button>
                                                    </li>
                                                @empty
                                                    <li><span class="dropdown-item-text text-muted">{{ __('No messages') }}</span></li>
                                                @endforelse
                                                {{-- go to the all show messages --}}
                                                <li><a href="{{ route('messages.index') }}" class="dropdown-item text-primary bg-white">Show All</a></li>
                                                    <li><button type="button" class="dropdown-item text-primary bg-white" data-bs-toggle="modal"
                                                        data-bs-target="#create-message-modal">Create Message</button></li>
                                            </ul>
                                            @endif
                                        </li>
                                    </ul>
                                    {{-- show the latest notifications --}}
                                    <ul class="navbar-icon-group">
                                        <li class="nav-item dropdown d-flex align-items-center category-nav mt-2">
                                            @guest
                                            <li class="nav-item">
                                                <a class="nav-link btn text-dark" href="{{ route('login') }}"><i class="fa-regular fa-bell dropdown-icon-lg" aria-hidden="true"></i></a>
                                            </li>
                                            @else
                                            <ul class="dropdown-menu border-primary rounded bg-success" aria-labelledby="notificationDropdown"
                                                style="max-height: 24rem; min-width: 18rem; overflow-y: auto;">
                                                <li class="text-center bg-success mb-1"><span class="h5 text-white">Notifications</span></li>
                                                @forelse ($notifications as $notification)
                                                    <li class="dropdown-content bg-white">
                                                        <a href="{{ $notification->type === 'community_message' && $notification->related_id
                                                            ? route('community.show', $notification->related_id) . '#chat'
                                                            : route('notifications.show', $notification) }}"
                                                            class="dropdown-item text-wrap border-bottom text-start {{ $notification->read_at ? '' : 'dropdown-item--unread fw-semibold' }}">
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
                                                <li class="bg-white"><a href="{{ route('notifications.index') }}" class="dropdown-item text-primary">{{ __('Show All') }}</a></li>
                                            </ul>
                                            <button id="notificationDropdown" class="nav-link dropdown-toggle text-dark no-arrow position-relative" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Notifications') }}">
                                                <i class="fa-regular fa-bell dropdown-icon-lg" aria-hidden="true"></i>
                                                @if ($unreadNotificationCount > 0)
                                                    <span class="badge rounded-pill bg-danger position-absolute bottom-0 end-0">{{ $unreadNotificationCount }}</span>
                                                @endif
                                            </button>
                                            @endguest
                                        </li>
                                    </ul>
                                    <ul class="navbar-icon-group">
                                        @guest
                                            <li class="nav-item">
                                                <a class="nav-link btn text-dark mt-2" href="{{ route('login') }}"><i class="fa-regular fa-calendar dropdown-icon-lg" aria-hidden="true"></i></a>
                                            </li>
                                        @else
                                            <li class="nav-item dropdown d-flex align-items-center category-nav mt-2">
                                                <a class="nav-link text-dark link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-calendar" href="#"> <i class="fa-regular fa-calendar dropdown-icon-lg"></i></a>
                                            </li>
                                        @endguest
                                    </ul>
                                     <ul class="navbar-icon-group">
                                        @guest
                                            <li class="nav-item">
                                                <a class="nav-link btn text-dark mt-2" href="{{ route('notifications.index', ['type' => 'community']) }}"><i class="fa-solid fa-people-group dropdown-icon-lg" aria-hidden="true"></i></a>
                                            </li>
                                        @else
                                            <li class="nav-item dropdown d-flex align-items-center category-nav mt-2">
                                                <button class="nav-link text-dark link-secondary text-decoration-none position-relative dropdown-toggle no-arrow" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false" aria-label="Community">
                                                    <i class="fa-solid fa-people-group dropdown-icon-lg" aria-hidden="true"></i>
                                                    @if ($communityNotificationCount > 0)
                                                        <span class="badge rounded-pill bg-danger position-absolute bottom-0 end-0">{{ $communityNotificationCount }}</span>
                                                    @endif
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end border-primary rounded bg-white"
                                                    style="max-height: 24rem; min-width: 20rem; overflow-y: auto;">
                                                    <li class="bg-primary text-center mb-1"><span class="h5 text-white">Community</span></li>
                                                    @forelse ($communityNotifications as $communityNotification)
                                                        <li class="dropdown-content bg-white">
                                                            <a href="{{ $communityNotification->type === 'community_message' && $communityNotification->related_id
                                                                ? route('community.show', $communityNotification->related_id) . '#chat'
                                                                : route('notifications.show', $communityNotification) }}"
                                                                class="dropdown-item text-wrap border-bottom text-start {{ $communityNotification->read_at ? '' : 'dropdown-item--unread fw-semibold' }}">
                                                                <span class="d-block">{{ $communityNotification->related?->name ?? __('Community') }}</span>
                                                                @if ($communityNotification->created_at)
                                                                    <time class="d-block small text-muted" datetime="{{ $communityNotification->created_at->toIso8601String() }}">
                                                                        {{ $communityNotification->created_at->format('M j, Y g:i A') }}
                                                                    </time>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @empty
                                                        <li><span class="dropdown-item-text text-muted">{{ __('No community notifications') }}</span></li>
                                                    @endforelse
                                                    <li class="bg-white"><a href="{{ route('notifications.index', ['type' => 'community']) }}" class="dropdown-item text-primary">{{ __('Show All') }}</a></li>
                                                </ul>
                                            </li>
                                        @endguest
                                    </ul>
                                <!-- Authentication Links -->
                                @guest
                                    @if (Route::has('login'))
                                        <li class="nav-item">
                                            <a class="nav-link mt-1" href="{{ route('login') }}">{{ __('Login') }}</a>
                                        </li>
                                    @endif

                                    @if (Route::has('register'))
                                        <li class="nav-item">
                                            <a class="nav-link mt-1" href="{{ route('register') }}">{{ __('Create Account') }}</a>
                                        </li>
                                    @endif
                                @else
                                    <a href="#" class="btn btn-primary text-white btn-create mt-2 d-inline-flex align-items-center justify-content-center  mt-3" data-bs-toggle="modal" data-bs-target="#modal-post">
                                        <span>CREATE</span>
                                    </a>
                                    <li class="nav-item dropdown dropdown-sm">

                                        <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-dark text-nowrap" href="#"
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
                                                    class="dropdown-item d-flex align-items-center gap-2 text-nowrap">
                                                    <i class="fa-solid fa-user-gear"></i> Admin
                                                </a>
                                            @endif

                                            <a href="{{ route('profile.show', Auth::user()->id) }}" class="dropdown-item d-flex align-items-center gap-2 text-nowrap">
                                                <i class="fa-solid fa-circle-user user-menu-icon"></i> Profile
                                            </a>

                                            <a href="{{ route('events.index', ['bookmark' => 1]) }}" class="dropdown-item d-flex align-items-center gap-2 text-nowrap">
                                                <i class="fa-solid fa-book-bookmark user-menu-icon"></i> Bookmark
                                            </a>
                                            @if ($hasMyEvents)
                                                <a href="{{ route('events.index', ['my_event' => 1]) }}"
                                                    class="{{ request()->boolean('my_event') ? 'active' : '' }} dropdown-item d-flex align-items-center gap-2 text-nowrap">
                                                    <i class="fa-solid fa-person-walking user-menu-icon"></i> My Event
                                                </a>
                                                
                                            @else
                                                <a href="{{ route('events.index')}}" class="dropdown-item d-flex align-items-center gap-2 text-nowrap">
                                                    <i class="fa-solid fa-person-walking user-menu-icon"></i> Activity
                                                </a>
                                            @endif
                                            
                                            @if ($hasMyCommunities)
                                                <a class="dropdown-item d-flex align-items-center gap-2 text-nowrap"
                                    href="{{ route('community.index', $hasMyCommunities ? ['filter' => 'owned'] : []) }}"><i class="fa-solid fa-people-group user-menu-icon"></i>My Community</span></a>
                                                
                                            @else
                                                <a href="{{ route('community.index')}}"
                                                    class="dropdown-item d-flex align-items-center gap-2 text-nowrap {{ request()->boolean('my_community') ? 'active' : '' }}">
                                                    <i class="fa-solid fa-people-group user-menu-icon"></i>Community
                                                </a>
                                            @endif
                                            {{-- <a href="{{ route('community.index') }}" class="dropdown-item d-flex align-items-center gap-2 text-nowrap">
                                                <i class="fa-solid fa-people-group user-menu-icon"></i> Community
                                            </a> --}}

                                            <a href="{{ route('notifications.index') }}" class="dropdown-item d-flex align-items-center gap-2 text-nowrap">
                                                <i class="fa-regular fa-message user-menu-icon"></i>
                                                Message
                                            </a>
                                            <hr class="dropdown-divider">

                                            <a class="dropdown-item d-flex align-items-center gap-2 text-nowrap" href="{{ route('logout') }}"
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
            </div>
        </nav>
