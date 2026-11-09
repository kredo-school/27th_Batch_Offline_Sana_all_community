@extends('layouts.app')

@section('title', 'Admin Users')

@section('content')
    <table class="table table-hover align-middle bg-white border text-secondary">
        <thead class="small text-secondary" style="--bs-table-bg:#f7cf05" >
            <tr>
                <th></th>
                <th>NAME</th>
                <th>EMAIL</th>
                <th>CREATED AT</th>
                <th>PAGE VIEWS</th>
                <th>CATEGORY VIEWS</th>
                <th>TOP CATEGORY</th>
                <th>STATUS</th>
                <th></th>
            </tr>
        </thead>
        <tbody class="table-hover">
            @foreach ($all_users as $user)
                <tr>
                    <td></td>
                    <td>
                        @if (Route::has('profile.show'))
                        <a href="{{ route('profile.show', $user->id) }}"
                            class="text-decoration-none text-dark fw-bold">{{ $user->name }}</a>
                        @else
                            <span class="text-dark fw-bold">{{ $user->name }}</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at }}</td>
                    @php
                        $topCategory = $categoryAnalytics->get($user->id, collect())->first();
                    @endphp
                    <td>{{ $pageViewStats->get($user->id)->page_view_count ?? 0 }}</td>
                    <td>{{ $categoryAnalytics->get($user->id, collect())->sum('view_count') }}</td>
                    <td>{{ $topCategory->name ?? '—' }}</td>
                    <td>
                        @if ($user->trashed())
                            <i class="fa-solid fa-circle text-secondary"></i>&nbsp; Inactive
                        @else
                            <i class="fa-solid fa-circle text-success"></i>&nbsp; Active
                        @endif
                    </td>
                    <td>
                        @if (Auth::user()->id !== $user->id)
                            <div class="dropdown">
                                <button type="submit" class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>

                                <div class="dropdown-menu">
                                    @if ($user->trashed())
                                        <button type="submit" class="dropdown-item text-success" data-bs-toggle="modal"
                                        data-bs-target="#activate-user-{{ $user->id }}">
                                        <i class="fa-solid fa-user-slash"></i>Activate {{ $user->name }}
                                    </button> 
                                    @else
                                       <button type="submit" class="dropdown-item text-danger" data-bs-toggle="modal"
                                        data-bs-target="#deactivate-user-{{ $user->id }}">
                                        <i class="fa-solid fa-user-slash"></i>Deactivate {{ $user->name }}
                                    </button> 
                                    @endif
                                </div>
                            </div>
                            {{-- include modal here --}}
                            @include('admin.users.modals.status')
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $all_users->links() }}

    @isset($recentPageViews)
        <div class="row mt-4">
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">直近のアクセス履歴</div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>閲覧URL</th>
                                    <th>IPアドレス</th>
                                    <th>日時</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentPageViews as $view)
                                    <tr>
                                        <td class="text-truncate" style="max-width: 300px;">{{ $view->url }}</td>
                                        <td><code>{{ $view->ip_address }}</code></td>
                                        <td>{{ \Carbon\Carbon::parse($view->created_at)->format('m/d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">アクセス履歴はありません。</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $recentPageViews->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endisset
@endsection
