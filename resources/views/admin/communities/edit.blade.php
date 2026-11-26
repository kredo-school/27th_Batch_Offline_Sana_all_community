@extends('layouts.app')

@section('title', __('Edit Community'))

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <h1 class="h3 mb-4 text-center">{{ __('Edit Community') }}</h1>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                <form action="{{ route('admin.communities.update', $community) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <input id="name" name="name" type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $community->name) }}" maxlength="150" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('Description') }}</label>
                        <textarea id="description" name="description" rows="4"
                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $community->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label for="owner_id" class="form-label">{{ __('Owner') }}</label>
                            <select id="owner_id" name="owner_id"
                                class="form-select @error('owner_id') is-invalid @enderror" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('owner_id', $community->owner_id) === $user->id)>
                                        {{ $user->username ?: __('Unknown user') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('owner_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">{{ __('Category') }}</label>
                            <select id="category" name="category"
                                class="form-select @error('category') is-invalid @enderror">
                                <option value="">{{ __('Select category') }}</option>
                                @foreach ($categoryOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category', $community->category) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">{{ __('Location') }}</label>
                            <input id="location" name="location" type="text"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location', $community->location) }}" maxlength="255">
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="language" class="form-label">{{ __('Language') }}</label>
                            <select id="language" name="language"
                                class="form-select @error('language') is-invalid @enderror" required>
                                @foreach (config('app.supported_locales') as $value => $label)
                                    <option value="{{ $value }}" @selected(old('language', $community->language) === $value)>
                                        {{ \App\Support\LocalizedValue::language($value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="visibility" class="form-label">{{ __('Visibility') }}</label>
                            <select id="visibility" name="visibility"
                                class="form-select @error('visibility') is-invalid @enderror" required>
                                @foreach (['public', 'approval', 'private'] as $option)
                                    <option value="{{ $option }}" @selected(old('visibility', $community->visibility) === $option)>
                                        {{ \App\Support\LocalizedValue::visibility($option) }}</option>
                                @endforeach
                            </select>
                            @error('visibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="guidelines" class="form-label">{{ __('Guidelines') }}</label>
                        <textarea id="guidelines" name="guidelines" rows="5"
                            class="form-control @error('guidelines') is-invalid @enderror">{{ old('guidelines', $community->guidelines) }}</textarea>
                        @error('guidelines')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">{{ __('Community icon') }}</label>
                            @if ($community->icon)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$community->icon) }}" alt="{{ $community->name }} icon"
                                        class="rounded border" width="72" height="72" style="object-fit: cover;">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="remove_icon" id="remove_icon" value="1">
                                    <label class="form-check-label" for="remove_icon">{{ __('Remove') }} {{ __('Community icon') }}</label>
                                </div>
                            @endif
                            <input id="icon" name="icon" type="file" accept="image/jpeg,image/png"
                                class="form-control @error('icon') is-invalid @enderror">
                            @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cover_image" class="form-label">{{ __('Cover image') }}</label>
                            @if ($community->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$community->image) }}" alt="{{ $community->name }} cover"
                                        class="rounded border w-100" style="max-height: 180px; object-fit: cover;">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="remove_cover_image" id="remove_cover_image" value="1">
                                    <label class="form-check-label" for="remove_cover_image">{{ __('Remove') }} {{ __('Cover image') }}</label>
                                </div>
                            @endif
                            <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png"
                                class="form-control @error('cover_image') is-invalid @enderror">
                            @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.communities') }}" class="btn btn-outline-warning btn-custom-outline">{{ __('Back to communities') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                    </div>
                </form>

                <hr class="my-4">
                <h2 class="h5 mb-3">{{ __('Organizer') }}</h2>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <strong>{{ $community->owner?->username ?? '-' }}</strong>
                    </div>
                    @if ($organizerCandidates->isNotEmpty())
                        <form action="{{ route('admin.communities.owner.replace', $community) }}" method="POST"
                            class="d-flex flex-wrap align-items-center gap-2"
                            data-confirm="true" data-confirm-action="{{ __('Remove') }}"
                            data-confirm-message="{{ __('Remove the organizer and make the selected member the new organizer?') }}">
                            @csrf
                            @method('PATCH')
                            <select name="new_owner_id" class="form-select form-select-sm @error('new_owner_id') is-invalid @enderror"
                                required aria-label="{{ __('New organizer') }}" style="min-width: 220px;">
                                <option value="" hidden>{{ __('Choose new organizer') }}</option>
                                @foreach ($organizerCandidates as $candidate)
                                    <option value="{{ $candidate->user->id }}">
                                        {{ $candidate->user->username ?: __('Unknown user') }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-danger btn-sm"
                                title="{{ __('Remove organizer') }}" aria-label="{{ __('Remove organizer') }}">
                                <i class="fa-solid fa-user-xmark" aria-hidden="true"></i>
                                {{ __('Remove organizer') }}
                            </button>
                            @error('new_owner_id')<div class="text-danger small w-100">{{ $message }}</div>@enderror
                        </form>
                    @else
                        <span class="small text-secondary">{{ __('Add an approved member before removing the organizer.') }}</span>
                    @endif
                </div>

                <h2 class="h5 mb-3">{{ __('Members') }}</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Joined') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($memberships as $membership)
                                <tr>
                                    <td>{{ $membership->user?->username ?? __('Deleted user') }}</td>
                                    <td>
                                        @if ((int) $membership->user_id !== (int) $community->owner_id)
                                            {{ $membership->role_label }}
                                        @else
                                            {{ __('Owner') }}
                                        @endif
                                    </td>
                                    <td>{{ $membership->status_label }}</td>
                                    <td>{{ \App\Support\LocalizedValue::dateTime($membership->joined_at, 'date') }}</td>
                                    <td>
                                        @if ((int) $membership->user_id !== (int) $community->owner_id)
                                            <div class="d-flex justify-content-end align-items-center gap-2">
                                                @if ($membership->status === 'approved')
                                                    <form action="{{ route('admin.communities.members.role', [$community, $membership->user]) }}"
                                                        method="POST" class="d-flex align-items-center gap-2">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="role" class="form-select form-select-sm" aria-label="{{ __('Role') }}">
                                                            <option value="member" @selected($membership->role === 'member')>{{ __('Member') }}</option>
                                                            <option value="moderator" @selected($membership->role === 'moderator')>{{ __('Moderator') }}</option>
                                                        </select>
                                                        <button type="submit" class="btn btn-outline-primary btn-sm"
                                                            title="{{ __('Save') }}" aria-label="{{ __('Save') }}">
                                                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('admin.communities.members.remove', [$community, $membership->user]) }}"
                                                    method="POST"
                                                    data-confirm="true"
                                                    data-confirm-action="{{ __('Remove') }}"
                                                    data-confirm-message="{{ __('Remove this community member?') }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                                        title="{{ __('Remove') }}" aria-label="{{ __('Remove') }}">
                                                        <i class="fa-solid fa-user-xmark" aria-hidden="true"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-3">{{ __('No members yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
