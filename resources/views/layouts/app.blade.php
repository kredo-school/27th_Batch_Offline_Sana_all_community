<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php
        // ページ側で @section('sidebar', 'community' または 'events') を指定するとサイドバーを出す
        // 指定のない events.* のページはイベント用のサイドバーを出す
        $sidebar = trim($__env->yieldContent('sidebar')) ?: (request()->routeIs('events.*') ? 'events' : '');
        $inCommunity = $sidebar === 'community' || request()->routeIs('community.*');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} | @yield('title')</title>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

    {{-- ヘッダー用のスタイル --}}
<style>
        /* コミュニティのページにいる間、上の COMMUNITY をオレンジにする */
        .nav-link.community-nav-active {
            color: #F58634;
            font-weight: 700;
        }

        .navbar-avatar {
            width: 36px;
            height: 36px;
            object-fit: cover;
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

        .category-nav .nav-link:hover,
        .category-nav .nav-link:focus-visible {
           color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus-visible {
            color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .btn-create{
            width: 6%;
            height: 20px;
            text-align: center !important;
        }

        .btn-custom-outline:hover,
        .btn-custom-outline:active {
            color: #ffffff !important;
        }

        .user-menu-icon{
            width: 20px;
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

    <style>
        .navbar-avatar {
            width: 36px;
            height: 36px;
            object-fit: cover;
        }

        .dropdown-item--unread {
            background-color: #e3f2ec !important;
        }

        .dropdown-item--unread:hover,
        .dropdown-item--unread:focus-visible {
            background-color: #cfe8dc !important;
        }

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

        /* .dropdown-menu{
            color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        } */
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
            display: flex;
            align-items: flex-end;
            gap: 4px;
            overflow-wrap: anywhere;
        }

        .message-chat-item--mine {
            align-self: flex-end;
            max-width: 100%;
        }

        .message-chat-bubble {
            max-width: 84%;
            padding: 8px 10px;
            border-radius: 8px 8px 8px 2px;
            background: #f0f3f1;
        }

        .message-chat-item--mine .message-chat-bubble {
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

        .message-chat-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 8px;
        }

        .message-chat-actions button {
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            padding: 2px 6px;
            border: 0;
            background: transparent;
            color: #68736f;
            font-size: 0.8rem;
        }

        .message-chat-actions button:hover,
        .message-chat-actions button:focus-visible {
            color: #174e45;
            outline: 2px solid #174e45;
            outline-offset: 2px;
        }

        .message-chat-edit {
            width: 100%;
            min-height: 64px;
            resize: vertical;
        }

        .no-arrow::after {
        display: none !important;
        }

        .dropdown-icon-lg {
            font-size: 20px;
        }

        .navbar-icon-group {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .navbar-icon-group .nav-link {
            padding-right: 0.35rem;
            padding-left: 0.35rem;
        }

        .navbar-icon-group + .navbar-icon-group {
            margin-left: -0.35rem;
        }

        .nav-icons{
            width: 100px;
            height: auto;
        }

        .category-nav .nav-link:hover,
        .category-nav .nav-link:focus-visible {
           color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .category-nav {
            flex-wrap: wrap;
        }

        .category-nav .category-nav-link {
            white-space: nowrap;
        }

        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus-visible {
            color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .btn-create{
            width: 6%;
            height: 20px;
            text-align: center !important;
        }

        .btn-custom-outline:hover,
        .btn-custom-outline:active {
            color: #ffffff !important;
        }

        .user-menu-icon{
            width: 20px;
            height: auto;
        }
        .all-navbar{
           
            left: 1px;
        }

        .header-list{

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

    @if ($sidebar !== '')
        <style>
            /* ナビゲーションバーの固定 */
            .navbar {
                position: sticky;
                top: 0;
                z-index: 1100;
                background-color: #fff;
            }

            /* モーダルは固定ヘッダーより手前に出す */
            .modal-backdrop {
                z-index: 1105;
            }

            .modal {
                z-index: 1110;
            }

            /* サイドバー付きページのレイアウト */
            .events-layout {
                display: flex;
                flex-wrap: nowrap;
                background-color: #F7F7F5;
                align-items: flex-start;
            }

            /* 左サイドバー */
            .event-sidebar-col {
                position: sticky;
                top: 68px; /* ナビゲーションバーの高さ分下から固定始動 */
                height: calc(100vh - 68px); /* 画面の高さからナビの高さを引いたサイズにする */
                overflow-y: auto; /* サイドバー内が多くなった場合にスクロールできるようにする */
                background-color: #fff;
                z-index: 100;
            }

            /* 本文側 */
            .event-content-col {
                flex: 1;
                min-width: 0;
            }
        </style>
    @endif
</head>

<body class="{{ request()->routeIs('profile.show') ? 'profile-overview-page' : '' }}">
    <div id="app">
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
                                <a class="nav-link {{ $inCommunity ? 'community-nav-active' : 'text-dark' }} header-list" href="{{ route('community.index', ['type' => 'community']) }}"><span class="h5 fw-normal">Community</span></a>
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

        <main
            class="{{ request()->routeIs('login', 'register') ? 'auth-main' : '' }} {{ request()->routeIs('admin.categories.create', 'admin.categories.edit', 'admin.events.edit', 'admin.communities.edit', 'admin.communities.show', 'admin.messages.show') ? 'category-form-main' : '' }}">
            @if ($sidebar !== '')
                <div class="container-fluid px-0">
                    <div class="row g-0 events-layout">
                        <aside class="col-md-3 col-lg-2 mb-4 mb-md-0 event-sidebar-col">
                            @include($sidebar === 'community' ? 'community.partials.sidebar' : 'events.partials.sidebar')
                        </aside>
                        <section class="col-md-9 col-lg-10 px-0 event-content-col">
                            @yield('content')
                        </section>
                    </div>
                </div>
            @else
                @yield('content')
            @endif
        </main>

        <div class="modal fade" id="action-confirmation-modal" tabindex="-1"
            aria-labelledby="action-confirmation-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="action-confirmation-title">Confirm action</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="action-confirmation-message"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-warning" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="action-confirmation-submit">Continue</button>
                    </div>
                </div>
            </div>
        </div>

        @auth
            <div class="modal fade" id="create-message-modal" tabindex="-1"
                aria-labelledby="create-message-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="create-message-title">Create Message</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            @foreach ([['title' => 'Following', 'users' => $followedMessageRecipients], ['title' => 'Others', 'users' => $otherMessageRecipients]] as $recipientGroup)
                                <section class="border-bottom last:border-bottom-0">
                                    <h3 class="px-3 py-2 mb-0 bg-light text-uppercase small fw-bold text-secondary">{{ $recipientGroup['title'] }}</h3>
                                    <div class="list-group list-group-flush">
                                        @forelse ($recipientGroup['users'] as $recipient)
                                            <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3"
                                                data-bs-dismiss="modal"
                                                data-message-chat-url="{{ route('messages.conversation', $recipient->id) }}"
                                                data-message-chat-send-url="{{ route('messages.send', $recipient->id) }}"
                                                data-message-chat-name="{{ $recipient->username }}">
                                                <span class="message-chat-avatar">
                                                    @if ($recipient->profile?->avatar)
                                                         @php
                                                    $avatarPath = $recipient->profile->avatar;
                                                    $avatarUrl = str_starts_with($avatarPath, 'https://') || str_starts_with($avatarPath, 'http://')
                                                        ? $avatarPath : asset('storage/'.$avatarPath);
                                                        @endphp
                                                        <img src="{{ $avatarUrl }}" alt="{{ $recipient->username ?? $recipient->name }}" class="rounded-circle navbar-avatar" width="36" height="36">
                                                    @else
                                                        {{ strtoupper(substr($recipient->name ?? '?', 0, 1)) }}
                                                    @endif
                                                </span>
                                                <span class="text-start">
                                                    <strong class="d-block">{{ $recipient->username ?? $recipient->name }}</strong>
                                                    {{-- @if ($recipient->username && $recipient->name !== $recipient->username)
                                                        <small class="text-muted">{{ $recipient->name }}</small>
                                                    @endif --}}
                                                </span>
                                            </button>
                                        @empty
                                            <p class="text-muted text-center my-3">No users in this group.</p>
                                        @endforelse
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
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
