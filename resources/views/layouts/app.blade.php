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
                            <a class="nav-link text-dark" href="{{ route('events.index') }}">EVENTS</a>
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

                        <ul>
                            <li class="nav-item d-flex align-items-center category-nav">
                                <a class="nav-link text-dark" href="#"> <i class="fa-regular fa-envelope"></i></a>
                                <a class="nav-link text-dark" href="#"><i class="fa-regular fa-clipboard"></i></a>
                                <a class="nav-link text-dark" href="#"> <i class="fa-regular fa-calendar"></i></a>
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
                            <a href="#" class="btn btn-success text-white">POST</a>
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
                                        <img src="{{ $avatarUrl }}" alt="{{ Auth::user()->name }}"
                                            class="rounded-circle">
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

                                    {{-- profile --}}
                                    <a href="{{ route('profile.show', Auth::user()->id) }}" class="dropdown-item">
                                        {{-- {{ route('profile.show', Auth::user()->id) }} --}}
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
