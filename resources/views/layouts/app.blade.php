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
    </style>

</head>

<body>
    <div id="app">
        @include('layouts.partials.header')

        <main
            class="{{ request()->routeIs('login', 'register') ? 'auth-main' : '' }} {{ request()->routeIs('admin.categories.create', 'admin.categories.edit', 'admin.events.edit', 'admin.communities.edit', 'admin.communities.show', 'admin.messages.show') ? 'category-form-main' : '' }}">
            @yield('content')
        </main>

        @include('layouts.partials.header-modals')
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
