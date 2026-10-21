<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} | @yield('title')</title>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <!-- FAVICON -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/onlylogo.jpg') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/mainimage.jpg') }}">

    <style>
        .auth-main {
            display: grid;
            min-height: calc(100svh - 68px);
            padding: 32px 16px;
            background: #f5f8f7;
        }

        .auth-main>.container {
            width: 100%;
            max-width: 960px;
        }

        .auth-main>.container>.row>.col-md-8 {
            width: 100%;
            max-width: 820px;
        }

        .auth-main .card,
        .category-form-panel .card {
            border-color: #d8e7e4;
            box-shadow: 0 12px 32px #173d3d12;
        }

        .auth-main .card-body,
        .category-form-panel .card-body {
            padding: clamp(1.25rem, 3vw, 2.5rem);
        }

        .category-form-main {
            display: grid;
            min-height: calc(100svh - 68px);
            padding: 32px 16px;
            background: #f5f8f7;
        }

        .category-form-panel {
            width: min(100%, 620px);
        }

        .message-chat-panel {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 1080;
            display: flex;
            width: min(360px, calc(100vw - 32px));
            height: min(480px, calc(100vh - 32px));
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #cbd8d3;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 10px 36px #102c2540;
        }

        .message-chat-panel[hidden] {
            display: none;
        }

        .message-chat-header {
            display: flex;
            min-height: 58px;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #174e45;
            color: #fff;
        }

        .message-chat-header h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .message-chat-close {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: inherit;
        }

        .message-chat-close:hover,
        .message-chat-close:focus-visible {
            background: #ffffff24;
        }

        .message-chat-status {
            padding: 8px 14px 0;
            color: #68736f;
            font-size: 0.875rem;
        }

        .message-chat-list {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            margin: 0;
            padding: 12px;
            list-style: none;
        }

        .message-chat-form {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 10px;
            border-top: 1px solid #dce4e0;
        }

        .message-chat-form textarea {
            min-height: 40px;
            max-height: 112px;
            resize: vertical;
        }

        .message-chat-form button {
            flex: 0 0 42px;
            height: 40px;
        }

        .message-chat-item {
            max-width: 84%;
            align-self: flex-start;
            padding: 8px 10px;
            border-radius: 8px 8px 8px 2px;
            background: #f0f3f1;
            overflow-wrap: anywhere;
        }

        .message-chat-item--mine {
            align-self: flex-end;
            border-radius: 8px 8px 2px 8px;
            background: #e1f1e9;
        }

        .message-chat-sender {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 5px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .message-chat-avatar {
            display: grid;
            width: 26px;
            height: 26px;
            flex: 0 0 26px;
            place-items: center;
            overflow: hidden;
            border-radius: 50%;
            background: #dce9e3;
            color: #174e45;
            font-size: 0.75rem;
        }

        .message-chat-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .message-chat-item p {
            margin: 0;
            white-space: pre-wrap;
        }

        .message-chat-item time {
            display: block;
            margin-top: 4px;
            color: #68736f;
            font-size: 0.72rem;
        }

        .no-arrow::after {
        display: none !important;
        }

        .dropdown-icon-lg {
            font-size: 20px;
        }

        .nav-icons{
            width: 100px;
            height: auto;
        }

        @media (max-width: 480px) {
            .message-chat-panel {
                right: 10px;
                bottom: 10px;
                width: calc(100vw - 20px);
                height: min(60vh, 480px);
            }
        }
    </style>

</head>

<body>
    <div id="app">
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

                <!-- search Navbar -->


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
                            <a class="nav-link text-dark" href="#">EVENTS</a>
                            <a class="nav-link text-dark" href="#">EXCHANGE</a>
                            <a class="nav-link text-dark" href="#">MEET UP</a>
                            <a class="nav-link text-dark" href="#">JOB</a>
                            <a class="nav-link text-dark" href="#">OTHER</a>
                            <a class="nav-link text-dark" href="#">COMMUNITY</a>
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
                                        </button>
                                        {{-- show latest messages --}}
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="messageDropdown"
                                            style="max-height: 24rem; min-width: 18rem; overflow-y: auto;">
                                            @forelse ($chats as $chat)
                                                <li>
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
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown"
                                            style="max-height: 24rem; min-width: 18rem; overflow-y: auto;">
                                            @forelse ($notifications as $notification)
                                                <li>
                                                    <a href="{{ route('notifications.show', $notification) }}"
                                                        class="dropdown-item text-wrap border-bottom text-start {{ $notification->read_at ? '' : 'fw-semibold bg-body-tertiary' }}">
                                                            <span class="d-block">{{ $notification->title ?: ucfirst(str_replace('_', ' ', $notification->type)) }}</span>
                                                            @if ($notification->sender_name)
                                                                <span class="d-block small text-muted">{{ __('From') }}: {{ $notification->sender_name }}</span>
                                                            @endif
                                                            @if ($notification->message)
                                                                <span class="d-block small text-secondary">{{ \Illuminate\Support\Str::limit($notification->message, 100) }}</span>
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
                                <a href="#" class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#modal-post">CREATE</a>
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
                                            <img src="{{ $avatarUrl }}" alt="{{ Auth::user()->name }}" class="rounded-circle">
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
                                            <i class="fa-solid fa-circle-user"></i> Profile
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

        <main
            class="{{ request()->routeIs('login', 'register') ? 'auth-main' : '' }} {{ request()->routeIs('admin.categories.create', 'admin.categories.edit') ? 'category-form-main' : '' }}">
            @yield('content')
        </main>

        @auth
            <aside id="message-chat-panel" class="message-chat-panel" role="dialog" aria-labelledby="message-chat-title" hidden>
                <header class="message-chat-header">
                    <h2 id="message-chat-title">{{ __('Conversation') }}</h2>
                    <button type="button" class="message-chat-close" data-close-message-chat aria-label="{{ __('Close conversation') }}">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </header>
                <div id="message-chat-status" class="message-chat-status" role="status" aria-live="polite"></div>
                <ol id="message-chat-list" class="message-chat-list" aria-live="polite"></ol>
                <form id="message-chat-form" class="message-chat-form">
                    <textarea id="message-chat-input" class="form-control" rows="1" maxlength="5000"
                        placeholder="{{ __('Write a message') }}" aria-label="{{ __('Write a message') }}" required></textarea>
                    <button type="submit" class="btn btn-success" aria-label="{{ __('Send message') }}">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    </button>
                </form>
            </aside>
        @endauth
    </div>
</body>
<footer class="bg-body-tertiary border-top mt-auto py-4 position-relative">
  <a href="#top" class="btn btn-primary position-absolute top-0 start-50 translate-middle rounded-circle shadow" style="width:44px;height:44px" aria-label="Back to top">
    <i class="fa-solid fa-angles-up text-white mt-2"></i>
  </a>
  <div class="container text-center pt-2">
    <ul class="list-inline small mb-2">
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" href="#">Home</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-term" href="#">Terms and Privacy</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-guide" href="#">Community Guidelines</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-report" href="#">Report & Content Removal Policy</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-event" href="#">Event organizer term</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-job" href="#">Job posting policy</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-adv" href="#">Business Advertising Policy</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-ptop" href="#">Peer-to-Peer Transaction Policy</a></li>
      <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-contact" href="#">Contact Us</a></li>


     
    </ul>
    <span class="small text-body-secondary">&copy; 2026 Sana All Community</span>
    @include('layouts.modals.contact')
    @include('layouts.modals.guide')
    @include('layouts.modals.terms')
    @include('layouts.modals.post')
    @include('layouts.modals.report')
    @include('layouts.modals.event')
    @include('layouts.modals.job')
    @include('layouts.modals.adv')
    @include('layouts.modals.p2p')
    @include('layouts.modals.calendar')
  </div>
</footer>
</html>
