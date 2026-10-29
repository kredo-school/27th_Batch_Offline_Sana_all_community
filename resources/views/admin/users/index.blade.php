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
                <th>SATATUS</th>
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
@endsection
