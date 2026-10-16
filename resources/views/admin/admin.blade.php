@extends('layouts.app')

@section('title', 'Admin Users')

@section('content')
    <style>
        .admin-dashboard {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: calc(100vh - 74px);
            color: var(--admin-ink);
            background: #f5f8f7;
        }

        .admin-sidebar {
            padding: 24px 14px;
            border-right: 1px solid var(--admin-line);
            background: #fff;
        }

        .admin-sidebar-title {
            margin: 0 10px 16px;
            color: var(--admin-muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-nav {
            display: grid;
            gap: 4px;
        }

        .admin-nav-link,
        .admin-nav-disabled {
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

        .admin-nav-link i,
        .admin-nav-disabled i {
            width: 17px;
            color: #668380;
            text-align: center;
        }

        .admin-nav-link:hover,
        .admin-nav-link.active {
            color: #126b6b;
            background: #d9f2ef;
        }

        .admin-nav-link.active {
            font-weight: 700;
        }

        .admin-nav-disabled {
            color: #98a9a7;
        }

        .admin-main {
            min-width: 0;
            padding: 28px clamp(16px, 3vw, 42px) 36px;
        }

        .admin-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .admin-eyebrow {
            margin: 0 0 5px;
            /* color: #348c89; */
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-heading h1 {
            margin: 0;
            font-size: 25px;
            font-weight: 700;
        }

        .admin-count {
            color: var(--admin-muted);
            font-size: 13px;
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

        .admin-table-wrap {
            overflow-x: auto;
            border: 1px solid #badbd5;
            border-radius: 5px;
            background: #fff;
        }

        .admin-table {
            min-width: 900px;
            margin: 0;
            color: var(--admin-ink);
            font-size: 13px;
        }

        .admin-table thead th {
            padding: 12px 13px;
            /* border-bottom: 1px solid #24aaa9;
            color: #103f40; */
            background: var(--admin-teal);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .admin-table tbody td {
            padding: 10px 13px;
            border-color: #c9e8df;
            vertical-align: middle;
            white-space: nowrap;
            background-color: transparent !important;
        }

        .admin-avatar {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            overflow: hidden;
            border: 1px solid #badbd5;
            border-radius: 50%;
            color: #126b6b;
            background: #fff;
            font-weight: 700;
        }

        .admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-action-buttons {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .admin-action-button {
            display: grid;
            width: 34px;
            height: 34px;
            padding: 0;
            place-items: center;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
        }

        .admin-badge.active {
            color: #176a42;
            background: #d1f0dc;
        }

        .admin-badge.inactive {
            color: #735f21;
            background: #f8e8b1;
        }

        .admin-badge.role-admin {
            color: #125f70;
            background: #d0eef3;
        }

        .admin-badge.role-user {
            color: #4e5f5c;
            background: #e6ece9;
        }

        .admin-empty {
            padding: 34px 12px !important;
            color: var(--admin-muted);
            text-align: center;
        }

        .admin-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 16px;
            color: var(--admin-muted);
            font-size: 13px;
        }

        .admin-pagination nav p {
            margin: 0;
        }

        @media (max-width: 760px) {
            .admin-dashboard {
                grid-template-columns: minmax(0, 1fr);
            }

            .admin-sidebar {
                padding: 10px 12px;
                border-right: 0;
                border-bottom: 1px solid var(--admin-line);
            }

            .admin-sidebar-title {
                margin-bottom: 8px;
            }

            .admin-nav {
                display: flex;
                overflow-x: auto;
            }

            .admin-nav-link,
            .admin-nav-disabled {
                flex: 0 0 auto;
            }

            .admin-main {
                padding: 22px 12px;
            }

            .admin-toolbar,
            .admin-pagination {
                align-items: stretch;
                flex-direction: column;
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

    <div class="admin-dashboard">
        @include('admin.partials.sidebar')

        <section class="admin-main">
            <header class="admin-heading">
                <div>
                    <p class="admin-eyebrow">Admin console</p>
                    <h1>User management</h1>
                </div>
                <span class="admin-count">{{ $all_users->total() }} users</span>
            </header>

            <form class="admin-toolbar" method="GET" action="{{ route('admin.users') }}">
                <div class="admin-search">
                    <label class="visually-hidden" for="user-search">Search users</label>
                    <input id="user-search" type="search" name="search" value="{{ request('search') }}"
                        placeholder="Search by name or email">
                    <button type="submit" aria-label="Search users" title="Search" class="btn btn-primary">
                        <i class="fa-solid fa-magnifying-glass text-white bg-primary"></i>
                    </button>
                </div>
                <div class="admin-sort">
                    <label for="user-sort">Sort by</label>
                    <select id="user-sort" name="sort" onchange="this.form.submit()">
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>Newest</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>Name A to Z</option>
                        <option value="name_desc" @selected(request('sort') === 'name_desc')>Name Z to A</option>
                    </select>
                </div>
            </form>

            <div class="admin-table-wrap">
                <table class="table admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th aria-label="Avatar"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Language</th>
                            <th>Status</th>
                            <th>Role</th>
                            <th aria-label="Actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($all_users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <span class="admin-avatar">
                                        @if ($user->profile?->avatar)
                                            <img src="{{ $user->profile->avatar }}" alt="{{ $user->name }}">
                                        @else
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        @endif
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at?->format('M d, Y') ?? '-' }}</td>
                                <td>{{ $user->preferred_language ?? '-' }}</td>
                                <td>
                                    <span class="admin-badge {{ $user->trashed() ? 'inactive' : 'active' }}">
                                        {{ $user->trashed() ? 'Inactive' : 'Active' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-badge {{ $user->role === 'admin' ? 'role-admin' : 'role-user' }}">
                                        {{ ucfirst($user->role ?? 'user') }}
                                    </span>
                                </td>
                                <td>
                                    @if (Auth::id() !== $user->id)
                                        <div class="admin-action-buttons">
                                            @if ($user->trashed())
                                                <button type="button" class="btn btn-outline-success btn-sm admin-action-button"
                                                    data-bs-toggle="modal" data-bs-target="#activate-user-{{ $user->id }}"
                                                    aria-label="Activate {{ $user->name }}" title="Activate user">
                                                    <i class="fa-solid fa-user-check"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-danger btn-sm admin-action-button"
                                                    data-bs-toggle="modal" data-bs-target="#deactivate-user-{{ $user->id }}"
                                                    aria-label="Deactivate {{ $user->name }}" title="Deactivate user">
                                                    <i class="fa-solid fa-user-slash"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-primary btn-sm admin-action-button"
                                                data-bs-toggle="modal" data-bs-target="#change-role-user-{{ $user->id }}"
                                                aria-label="{{ $user->role === 'admin' ? 'Change to user' : 'Make admin' }} for {{ $user->name }}"
                                                title="{{ $user->role === 'admin' ? 'Change to user' : 'Make admin' }}">
                                                <i class="fa-solid fa-user-gear"></i>
                                            </button>
                                            </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="admin-empty" colspan="9">No users match this search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="admin-pagination">
                <span>Showing {{ $all_users->firstItem() ?? 0 }}–{{ $all_users->lastItem() ?? 0 }} of {{ $all_users->total() }}</span>
                {{ $all_users->links() }}
            </div>
        </section>
    </div>

    @foreach ($all_users as $user)
        @if (Auth::id() !== $user->id)
            @include('admin.users.modals.status')
            @include('admin.users.modals.role')
        @endif
    @endforeach
@endsection
