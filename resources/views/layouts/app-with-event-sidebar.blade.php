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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- custom css --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- FAVICON -->
    <link rel="icon" type="image/jpeg" href="{{ asset('images/onlylogo.jpg') }}">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/mainimage.jpg') }}">

    @include('layouts.partials.header-styles')

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

        .auth-main .card {
            border-color: #d8e7e4;
            box-shadow: 0 12px 32px #173d3d12;
        }

        .auth-main .card-body {
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

        .category-form-panel .card {
            border-color: #d8e7e4;
            box-shadow: 0 12px 32px #173d3d12;
        }

        .category-form-panel .card-body {
            padding: clamp(1.25rem, 3vw, 2.5rem);
        }

        main.container-fluid {
            background-color: #F7F7F5;
        }

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

        /* イベント一覧ページのレイアウト */
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

        /* イベント一覧側 */
        .event-content-col {
            flex: 1;
            min-width: 0;
        }
    </style>

</head>

<body>
    @php
        $inCommunity = request()->routeIs('community.*')
            || (isset($event) && $event instanceof \App\Models\Event && $event->community_id);
    @endphp
    <div id="app">
        @include('layouts.partials.header')

        <div
            class="{{ request()->routeIs('login', 'register') ? 'auth-main' : '' }} {{ request()->routeIs('admin.categories.create', 'admin.categories.edit') ? 'category-form-main' : '' }}">
            <div class="container-fluid px-0">
                <div class="row g-0 events-layout">
                    <aside class="col-md-3 col-lg-2 mb-4 mb-md-0 event-sidebar-col">
                        {{-- コミュニティのページとコミュニティ内のイベントではコミュニティ用のサイドバーを出す --}}
                        @if ($inCommunity)
                            @include('community.partials.sidebar')
                        @else
                            @include('events.partials.sidebar')
                        @endif
                    </aside>
                    <section class="col-md-9 col-lg-10 px-0 event-content-col">
                        @yield('content')
                    </section>
                </div>
            </div>
        </div>

        @include('layouts.partials.header-modals')
        @include('layouts.modals.post')
        @include('layouts.modals.calendar')
    </div>
</body>
<footer class="bg-body-tertiary border-top mt-auto py-4 position-relative">
    <a href="#top" class="btn btn-primary position-absolute top-0 start-50 translate-middle rounded-circle shadow"
        style="width:44px;height:44px" aria-label="Back to top">
        <i class="fa-solid fa-angles-up text-white mt-2"></i>
    </a>
    <div class="container text-center pt-2">
        <ul class="list-inline small mb-2">
            <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" href="#">Home</a>
            </li>
            <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" href="#">Terms and
                    Privacy</a></li>
            <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" href="#">Community
                    Guidelines</a></li>
            <li class="list-inline-item mx-2"><a class="link-secondary text-decoration-none" href="#">Contact
                    Us</a></li>
        </ul>
        <span class="small text-body-secondary">&copy; 2026 Sana All Community</span>
    </div>
</footer>

</html>
