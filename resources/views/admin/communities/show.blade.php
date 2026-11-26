@extends('layouts.app')

@section('title', __('Community Details'))

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <a href="{{ route('admin.communities') }}" class="small text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>{{ __('Back to communities') }}
                </a>

                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mt-3 mb-4">
                    <div>
                        <h1 class="h3 mb-2">{{ $community->name }}</h1>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-{{ $community->visibility === 'public' ? 'primary' : 'dark' }}">
                                {{ $community->visibility_label }}
                            </span>
                            <span class="badge bg-secondary">{{ $community->language_label }}</span>
                            @if ($community->category)
                                <span class="badge bg-success">{{ $community->localized_category }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.communities.edit', $community) }}" class="btn btn-outline-primary btn-sm"
                            title="{{ __('Edit') }}" aria-label="{{ __('Edit community') }}">
                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                @if ($community->image)
                    <img class="img-fluid rounded border mb-4" src="{{ asset('storage/'.$community->image) }}"
                        alt="{{ $community->name }} cover" style="max-height: 300px; width: 100%; object-fit: cover;">
                @endif

                @if ($community->description)
                    <p style="white-space: pre-wrap;">{{ $community->description }}</p>
                @endif

                <dl class="row mb-4">
                    <dt class="col-sm-4">{{ __('Community') }} ID</dt>
                    <dd class="col-sm-8">{{ $community->id }}</dd>
                    <dt class="col-sm-4">{{ __('Owner') }}</dt>
                    <dd class="col-sm-8">{{ $community->owner?->username ?? '-' }}</dd>
                    <dt class="col-sm-4">{{ __('Members') }}</dt>
                    <dd class="col-sm-8">{{ $community->members_count }}</dd>
                    <dt class="col-sm-4">{{ __('Events') }}</dt>
                    <dd class="col-sm-8">{{ $community->events_count }}</dd>
                    @if ($community->category)
                        <dt class="col-sm-4">{{ __('Category') }}</dt>
                        <dd class="col-sm-8">{{ $community->localized_category }}</dd>
                    @endif
                    <dt class="col-sm-4">{{ __('Location') }}</dt>
                    <dd class="col-sm-8">{{ $community->location ?: '-' }}</dd>
                    <dt class="col-sm-4">{{ __('Visibility') }}</dt>
                    <dd class="col-sm-8">{{ $community->visibility_label }}</dd>
                    <dt class="col-sm-4">{{ __('Language') }}</dt>
                    <dd class="col-sm-8">{{ $community->language_label }}</dd>
                    <dt class="col-sm-4">{{ __('Guidelines') }}</dt>
                    <dd class="col-sm-8" style="white-space: pre-wrap;">{{ $community->guidelines ?: '-' }}</dd>
                    <dt class="col-sm-4">{{ __('Community icon') }}</dt>
                    <dd class="col-sm-8">
                        @if ($community->icon)
                            <img src="{{ asset('storage/'.$community->icon) }}" alt="{{ $community->name }} icon"
                                class="rounded border" width="72" height="72" style="object-fit: cover;">
                        @else
                            -
                        @endif
                    </dd>
                    <dt class="col-sm-4">{{ __('Created') }}</dt>
                    <dd class="col-sm-8">{{ \App\Support\LocalizedValue::dateTime($community->created_at) }}</dd>
                    <dt class="col-sm-4">{{ __('Updated') }}</dt>
                    <dd class="col-sm-8">{{ \App\Support\LocalizedValue::dateTime($community->updated_at) }}</dd>
                </dl>

                <h2 class="h5">{{ __('Members') }}</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Joined') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($memberships as $membership)
                                <tr>
                                    <td>{{ $membership->user?->username ?? __('Deleted user') }}</td>
                                    <td>{{ $membership->role_label }}</td>
                                    <td>{{ $membership->status_label }}</td>
                                    <td>{{ \App\Support\LocalizedValue::dateTime($membership->joined_at, 'date') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-3">{{ __('No members yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h2 class="h5 mt-4">{{ __('Events') }}</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Visibility') }}</th>
                                <th>{{ __('Starts') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($community->events as $event)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.events.show', $event) }}" class="text-decoration-none">
                                            {{ $event->title }}
                                        </a>
                                    </td>
                                    <td>{{ $event->status_label }}</td>
                                    <td>{{ $event->visibility_label }}</td>
                                    <td>{{ \App\Support\LocalizedValue::dateTime($event->start_at) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-3">{{ __('No events yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
