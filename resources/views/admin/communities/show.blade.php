@extends('layouts.app')

@section('title', 'Community Details')

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <a href="{{ route('admin.communities') }}" class="small text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Back to communities
                </a>

                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
                    <div>
                        <h1 class="h3 mb-2">{{ $community->name }}</h1>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-{{ $community->visibility === 'public' ? 'primary' : 'dark' }}">
                                {{ ucfirst($community->visibility) }}
                            </span>
                            <span class="badge bg-secondary">{{ strtoupper($community->language) }}</span>
                        </div>
                    </div>
                    <a href="{{ route('admin.communities.edit', $community) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-pen me-1" aria-hidden="true"></i>Edit community
                    </a>
                </div>

                @if ($community->description)
                    <p style="white-space: pre-wrap;">{{ $community->description }}</p>
                @endif

                <dl class="row mb-4">
                    <dt class="col-sm-4">Owner</dt>
                    <dd class="col-sm-8">{{ $community->owner?->name ?? '-' }}</dd>
                    <dt class="col-sm-4">Members</dt>
                    <dd class="col-sm-8">{{ $community->members_count }}</dd>
                    <dt class="col-sm-4">Events</dt>
                    <dd class="col-sm-8">{{ $community->events_count }}</dd>
                    <dt class="col-sm-4">Created</dt>
                    <dd class="col-sm-8">{{ $community->created_at?->format('M d, Y H:i') }}</dd>
                </dl>

                <h2 class="h5">Members</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($memberships as $membership)
                                <tr>
                                    <td>{{ $membership->user?->username ?? $membership->user?->name ?? 'Deleted user' }}</td>
                                    <td>{{ ucfirst($membership->role) }}</td>
                                    <td>{{ ucfirst($membership->status) }}</td>
                                    <td>{{ $membership->joined_at?->format('M d, Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-3">No members yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
