<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
   <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                  <h1 class="h5 mb-0">{{config('app.name')}}</h1>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    {{-- [SOON] search bar here. show it when users logs in --}}
                    @auth
                        {{-- this will not show up in the admin side --}}
                        @if (!request()->is('admin/*'))
                            <div class="navbar-nav ms-auto">
                                <form action="{{ route('search')}}" style="max-width: 800px" class="d-flex align-items-center gap-1">
                                    <input type="search" name="search" id="search" placeholder="Search..." class="form-control form-control-sm flex-grow-1">
                                    <button type="submit" name="type" value="user"class="btn btn-primary btn-sm text-nowrap">Search user</button>
                                    <button type="submit" name="type" value="category" class="btn btn-secondary btn-sm text-nowrap">Search category</button>
                                    <button type="submit" name="type" value="post"class="btn btn-success btn-sm text-nowrap">Search post</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                                {{-- Home  --}}
                                <li class="nav-item" title="Home">
                                    <a href="{{ route('index')}}" class="nav-link">
                                        <i class="fa-solid fa-house text-dark icon-sm"></i>
                                    </a>
                                </li>
                                {{-- Create Posts --}}
                                <li class="nav-item">
                                    <a href="{{ route('post.create') }}" class="nav-link">
                                        <i class="fa-solid fa-circle-plus text-dark icon-sm"></i>
                                    </a>
                                    
                                </li>

                                {{-- Account --}}
                                <li class="nav-item dropdown">
                                <button id="account-dropdown" class="btn shadow-none nav-link" data-bs-toggle="dropdown">
                                    @if (Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="rounded-circle avatar-sm">

                                    @else
                                    <i class="fa-solid fa-circle-user text-dark icon-sm"></i>                                    
                                    @endif
                                </button>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="account-dropdown">

                                    {{-- SOON admin controles --}}
                                    @can('admin')
                                         <a href="{{ route('admin.users') }}" class="dropdown-item">
                                        <i class="fa-solid fa-user-gear"></i> Admin
                                        </a>

                                        <hr class="dropdown-divider">
                                    @endcan
                                   
                                    {{-- Profile --}}
                                    <a href="{{ route('profile.show', Auth::user()->id) }}" class="dropdown-item">
                                        <i class="fa-solid fa-circle-user"></i> Profile
                                    </a>

                                    {{-- logout --}}
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                                     <i class="fa-solid fa-right-from-bracket"></i>
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-5">
            <div class="container">
                <div class="row justify-content-center">
                    {{-- [SOON] Admin Menu (col-3) --}}
                    @if (request()->is('admin/*'))
                        <div class="col-3">
                            <div class="list-group">
                                <a href="{{ route('admin.users')}}" class="list-group-item {{ request()->is('admin/users') ? 'active' : ''}}">
                                    <i class="fa-solid fa-users"></i> Users
                                </a>
                                <a href="{{ route('admin.posts') }}" class="list-group-item {{ request()->is('admin/posts') ? 'active' : ''}}">
                                    <i class="fa-solid fa-newspaper"></i> Posts
                                </a>
                                <a href="{{ route('admin.categories')}}" class="list-group-item {{ request()->is('admin/categories') ? 'active' : ''}}">
                                    <i class="fa-solid fa-tags"></i> Categories
                                </a>
                            </div>
                        </div>
                    @endif
                    <div class="col-9">
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
