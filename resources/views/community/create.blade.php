@extends('layouts.app')

@section('sidebar', 'community')

@section('title', $title ?? __('Create Community'))

@section('content')
@php
    $editing = $editing ?? false;
@endphp
<style>
    /* イベント作成ページ (events/create.blade.php) と同じ見た目に揃える。ボタン以外の青はコミュニティのオレンジにする */
    .community-accent {
        color: #F58634;
    }
    .event-form-section {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 20px;
    }
    .event-form-step {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fdebdc;
        color: #F58634;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .event-form-label {
        font-weight: 600;
        font-size: 14px;
    }
    .event-form-required {
        color: #F58634;
        font-size: 11px;
    }
    .event-form-help {
        color: #888780;
        font-size: 12px;
    }
    .event-option {
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 14px 16px;
        cursor: pointer;
        display: block;
    }
    .event-option:has(input:checked) {
        background: #fdebdc;
        border-color: #F58634;
    }
    /* ラジオ・チェックボックスの選択色と入力欄のフォーカス色もオレンジ */
    .event-form-section .form-check-input:checked,
    .form-check-input#terms:checked {
        background-color: #F58634;
        border-color: #F58634;
    }
    .event-form-section .form-control:focus,
    .event-form-section .form-select:focus,
    .form-check-input:focus {
        border-color: #f9b27c;
        box-shadow: 0 0 0 0.25rem rgba(245, 134, 52, 0.25);
    }
</style>

<div class="container py-4" style="max-width: 860px;">

    {{-- list --}}
    <nav class="mb-4 small">
        <a href="{{ route('community.index') }}" class="text-decoration-none fw-semibold text-dark">Communities</a>
        <span class="mx-2">&gt;</span>
        {{-- 今開いているページはオレンジ --}}
        <span class="community-accent fw-semibold" aria-current="page">{{ $editing ? 'Edit community' : 'Create a community' }}</span>
    </nav>

    {{-- title --}}
    <p class="community-accent fw-semibold small mb-1">{{ $editing ? 'Community settings' : 'New community' }}</p>
    <h1 class="fw-normal mb-1">{{ $editing ? __('Edit').' '.__('Community') : __('Create Community') }}</h1>
    <p class="event-form-help mb-4">
        {{ $editing ? 'Update the details below. Changes are visible to all members immediately.' : 'Fill in the details below to start your community. You will become the organizer.' }}
    </p>

    <form action="{{ $editing ? route('community.update', $community->id) : route('community.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        {{-- 01 Community basics --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">01</div>
                <div>
                    <h5 class="mb-0">Community basics</h5>
                    <p class="event-form-help mb-0">Please give a clear name and description.</p>
                </div>
            </div>

            {{-- Name --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label for="name" class="event-form-label">Community name</label>
                    <span class="event-form-required">REQUIRED</span>
                </div>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $community->name ?? '') }}" maxlength="60" placeholder="e.g. Tokyo Creators" required>
                <small class="event-form-help">Maximum 60 characters.</small>
                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="row g-4">
                {{-- Category --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="category" class="event-form-label">Category</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                        <option value="" hidden>Select category</option>
                        @foreach (\App\Models\Community::CATEGORIES as $category)
                            <option value="{{ $category }}" @selected(old('category', $community->category ?? '') === $category)>
                                {{ \App\Models\Community::getLocalizedCategoryName($category) }}
                            </option>
                        @endforeach
                    </select>
                    @error('category') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Location --}}
                <div class="col-md-6">
                    <label for="location" class="event-form-label">Location</label>
                    <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror"
                           value="{{ old('location', $community->location ?? '') }}" maxlength="255" placeholder="e.g. Cebu City or Online">
                    @error('location') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Description --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between">
                    <label for="description" class="event-form-label">Description</label>
                    <span class="event-form-required">REQUIRED</span>
                </div>
                <textarea name="description" id="description" rows="4" maxlength="500"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="What is this community about? Who should join?" required>{{ old('description', $community->description ?? '') }}</textarea>
                <small class="event-form-help">Help people understand what to expect. Maximum 500 characters.</small>
                @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- 02 Community identity --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">02</div>
                <div>
                    <h5 class="mb-0">Community identity</h5>
                    <p class="event-form-help mb-0">Please add visuals that help this community stand out.</p>
                </div>
            </div>

            <div class="row g-4">
                {{-- Icon --}}
                <div class="col-md-4">
                    <label for="community-icon" class="event-form-label">Community icon</label>
                    {{-- アイコンのプレビュー枠（写真全体を枠内に収める） --}}
                    <div class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden mb-2" style="width: 120px; height: 120px;">
                        <img data-preview="community-icon" src="{{ $editing && ($community->icon ?? null) ? asset('storage/' . $community->icon) : '' }}" alt="Community icon preview" class="w-100 h-100 object-fit-contain @if (! ($editing && ($community->icon ?? null))) d-none @endif">
                        <i data-preview-empty="community-icon" class="fa-regular fa-image fs-2 text-secondary @if ($editing && ($community->icon ?? null)) d-none @endif"></i>
                    </div>
                    <input id="community-icon" name="icon" type="file" accept="image/png,image/jpeg" class="form-control @error('icon') is-invalid @enderror">
                    <small class="event-form-help">Square JPG or PNG, up to 5MB.</small>
                    @error('icon') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Cover image --}}
                <div class="col-md-8">
                    <label for="community-cover-image" class="event-form-label">Cover image</label>
                    {{-- カバー画像のプレビュー枠（写真の縦横比に合わせて高さが変わる） --}}
                    <div class="border rounded-3 bg-white d-flex align-items-center justify-content-center overflow-hidden mb-2">
                        <img data-preview="community-cover-image" src="{{ $editing && ($community->image ?? null) ? asset('storage/' . $community->image) : '' }}" alt="Cover image preview" class="d-block w-100 object-fit-contain @if (! ($editing && ($community->image ?? null))) d-none @endif" style="max-height: 320px;">
                        <i data-preview-empty="community-cover-image" class="fa-regular fa-image fs-2 text-secondary my-5 @if ($editing && ($community->image ?? null)) d-none @endif"></i>
                    </div>
                    <input id="community-cover-image" name="cover_image" type="file" accept="image/png,image/jpeg" class="form-control @error('cover_image') is-invalid @enderror">
                    <small class="event-form-help">JPG or PNG, up to 5MB. Recommended size: 1600 × 600 px.</small>
                    @error('cover_image') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- 03 Visibility --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">03</div>
                <div>
                    <h5 class="mb-0">Visibility</h5>
                    <p class="event-form-help mb-0">Please select who can find and join this community.</p>
                </div>
            </div>

            <div class="d-grid gap-2">
                @foreach ([
                    'public' => ['Public community', 'Anyone can find, view and join this community.'],
                    'approval' => ['Approval required', 'Anyone can find this community, but you approve new members.'],
                    'private' => ['Private community', 'Only invited people can find and join this community.'],
                ] as $value => [$label, $help])
                    <label class="event-option">
                        <input type="radio" name="visibility" value="{{ $value }}" class="form-check-input me-2" @checked(old('visibility', $community->visibility ?? 'public') === $value)>
                        <strong>{{ $label }}</strong>
                        <div class="event-form-help ms-4">{{ $help }}</div>
                    </label>
                @endforeach
            </div>
            @error('visibility') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        {{-- 04 Guidelines --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">04</div>
                <div>
                    <h5 class="mb-0">Guidelines</h5>
                    <p class="event-form-help mb-0">Please set expectations for positive and respectful participation.</p>
                </div>
            </div>

            <label for="guidelines" class="event-form-label">Welcome message and guidelines</label>
            <textarea name="guidelines" id="guidelines" rows="5" maxlength="5000"
                      class="form-control @error('guidelines') is-invalid @enderror"
                      placeholder="Share a welcome note, community values, or important rules...">{{ old('guidelines', $community->guidelines ?? '') }}</textarea>
            @error('guidelines') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        @unless ($editing)
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label event-form-help" for="terms">
                    I agree to the
                    <a href="#" class="community-accent" data-bs-toggle="modal" data-bs-target="#modal-guide">Community Guidelines</a>
                    and
                    <a href="#" class="community-accent" data-bs-toggle="modal" data-bs-target="#modal-term">Terms of Service</a>.
                </label>
            </div>
        @endunless

        {{-- button --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ $editing ? route('community.show', $community->id) : route('community.index') }}" class="btn btn-outline-secondary px-4">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary px-4">{{ $editing ? __('Save') : __('Create Community') }}</button>
        </div>
    </form>

    @if ($editing)
        {{-- 削除（フォームの外に置く） --}}
        <div class="event-form-section mt-4 d-flex justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-0 text-danger">Delete community</h5>
                <p class="event-form-help mb-0">This action cannot be undone.</p>
            </div>
            <form action="{{ route('community.destroy', $community->id) }}" method="POST"
                onsubmit="return confirm('このコミュニティを削除しますか？この操作は取り消せません。');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger px-4">Delete community</button>
            </form>
        </div>
    @endif
</div>


<script>
    // 選択した画像をプレビュー枠に表示する
    ['community-icon', 'community-cover-image'].forEach(id => {
        const input = document.getElementById(id);
        const preview = document.querySelector(`[data-preview="${id}"]`);
        const empty = document.querySelector(`[data-preview-empty="${id}"]`);
        let objectUrl;

        input.addEventListener('change', event => {
            const file = event.target.files[0];
            if (!file) return;
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.classList.remove('d-none');
            empty.classList.add('d-none');
        });
    });
</script>
@endsection
