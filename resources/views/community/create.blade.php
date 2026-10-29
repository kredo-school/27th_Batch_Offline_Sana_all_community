@extends('layouts.app')

@section('title', $title ?? 'Create Community')

@section('content')
<div class="container py-4">
    <div class="create-page">
        
        <!-- Breadcrumb -->
        <div class="create-breadcrumb mb-3">
            <a href="{{ route('community.index') }}" class="text-decoration-none">Communities</a>
            <i class="fa-solid fa-chevron-right mx-1 small text-muted"></i>
            <span class="fw-bold text-dark">{{ $title ?? 'Create Community' }}</span>
        </div>

        <!-- Heading -->
        <div class="create-heading mb-4">
            <p class="eyebrow accent text-primary fw-bold small mb-1">
                {{ ($editing ?? false) ? 'COMMUNITY SETTINGS' : 'NEW COMMUNITY' }}
            </p>
            <h1 class="fw-bold display-6 text-dark">
                {{ ($editing ?? false) ? 'Edit your community.' : 'Bring your people together.' }}
            </h1>
            <p class="text-muted">
                {{ ($editing ?? false) ? 'Keep your community information accurate and make it easy for new members to understand what you are about.' : 'Start a space where members can connect, chat, and organize events around a shared interest.' }}
            </p>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="validation-errors alert alert-danger shadow-sm" role="alert">
                <strong>Please check the form.</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form class="create-form" action="{{ ($editing ?? false) ? route('community.update', $community->id) : route('community.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($editing ?? false) 
                @method('PUT') 
            @endif

            <div class="form-content">
                
                <!-- Section 01: Community Basics -->
                <section class="form-section card bg-white mb-4 p-4 border shadow-sm rounded-3">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary text-white rounded-circle p-2 me-2 fw-bold" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">01</span>
                        <h2 class="h5 fw-bold mb-0 text-dark">Community basics</h2>
                    </div>
                    <p class="text-muted small mb-4">Give your community a clear name and description.</p>

                    <div class="field-grid row g-3">
                        <!-- Community Name -->
                        <div class="field full-field col-12">
                            <label class="form-label fw-bold small text-dark">Community name <span class="text-danger">*</span></label>
                            <input class="form-control border-secondary-subtle" required name="name" maxlength="60" placeholder="e.g. Tokyo Creators" value="{{ old('name', ($editing ?? false) ? ($community->name ?? 'Tokyo Creators') : '') }}">
                            <small class="text-muted">Choose a name that is easy to recognize. Maximum 60 characters.</small>
                        </div>

                        <!-- Category -->
                        <div class="field col-md-6">
                            <label class="form-label fw-bold small text-dark">Category <span class="text-danger">*</span></label>
                            <select class="form-select border-secondary-subtle" required name="category">
                                <option value="" disabled @selected(!old('category') && !($editing ?? false))>Select a category</option>
                                @foreach (['Creative', 'Technology', 'Outdoors', 'Learning', 'Wellness', 'Other'] as $category)
                                    <option value="{{ $category }}" @selected(old('category', ($editing ?? false) ? ($community->category ?? 'Creative') : '') === $category)>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="field col-md-6">
                            <label class="form-label fw-bold small text-dark">Location</label>
                            <input class="form-control border-secondary-subtle" name="location" placeholder="e.g. Tokyo or Online" value="{{ old('location', ($editing ?? false) ? ($community->location ?? '') : '') }}">
                        </div>

                        <!-- Description -->
                        <div class="field full-field col-12">
                            <label class="form-label fw-bold small text-dark">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control border-secondary-subtle" required name="description" rows="5" maxlength="500" placeholder="What is this community about? Who should join?">{{ old('description', ($editing ?? false) ? ($community->description ?? '') : '') }}</textarea>
                            <small class="text-muted">Help people understand what to expect. Maximum 500 characters.</small>
                        </div>
                    </div>
                </section>

                <!-- Section 02: Community Identity (Icon & Cover Image) -->
                <section class="form-section card bg-white mb-4 p-4 border shadow-sm rounded-3">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary text-white rounded-circle p-2 me-2 fw-bold" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">02</span>
                        <h2 class="h5 fw-bold mb-0 text-dark">Community identity</h2>
                    </div>
                    <p class="text-muted small mb-4">Add a visual that helps your community stand out.</p>

                    <div class="row g-3">
                        <!-- Community Icon -->
                        <div class="col-md-4">
                            <label for="community-icon" class="form-label fw-bold small text-dark">Community icon</label>
                            <!-- アイコンのプレビュー枠（写真全体を枠内に収める） -->
                            <div class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden mb-2" style="width: 120px; height: 120px;">
                                <img data-preview="community-icon" src="{{ ($editing ?? false) && ($community->icon ?? null) ? asset('storage/' . $community->icon) : '' }}" alt="Community icon preview" class="w-100 h-100 object-fit-contain @if (! (($editing ?? false) && ($community->icon ?? null))) d-none @endif">
                                <i data-preview-empty="community-icon" class="fa-regular fa-image fs-2 text-secondary @if (($editing ?? false) && ($community->icon ?? null)) d-none @endif"></i>
                            </div>
                            <input id="community-icon" name="icon" type="file" accept="image/png,image/jpeg" class="form-control">
                            <small class="form-text text-muted">Square JPG/PNG, up to 5 MB</small>
                        </div>

                        <!-- Cover Image -->
                        <div class="col-md-8">
                            <label for="community-cover-image" class="form-label fw-bold small text-dark">Cover image</label>
                            <!-- カバー画像のプレビュー枠（写真の縦横比に合わせて高さが変わる） -->
                            <div class="border rounded-3 bg-white d-flex align-items-center justify-content-center overflow-hidden mb-2">
                                <img data-preview="community-cover-image" src="{{ ($editing ?? false) && ($community->image ?? null) ? asset('storage/' . $community->image) : '' }}" alt="Cover image preview" class="d-block w-100 object-fit-contain @if (! (($editing ?? false) && ($community->image ?? null))) d-none @endif" style="max-height: 320px;">
                                <i data-preview-empty="community-cover-image" class="fa-regular fa-image fs-2 text-secondary my-5 @if (($editing ?? false) && ($community->image ?? null)) d-none @endif"></i>
                            </div>
                            <input id="community-cover-image" name="cover_image" type="file" accept="image/png,image/jpeg" class="form-control">
                            <small class="form-text text-muted">JPG/PNG, up to 5 MB. Recommended size: 1600 × 600 px.</small>
                        </div>
                    </div>
                </section>

                <!-- Section 03: Privacy and Access -->
                <section class="form-section card bg-white mb-4 p-4 border shadow-sm rounded-3">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary text-white rounded-circle p-2 me-2 fw-bold" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">03</span>
                        <h2 class="h5 fw-bold mb-0 text-dark">Privacy and access</h2>
                    </div>
                    <p class="text-muted small mb-4">Choose who can find and join your community.</p>

                    <div class="privacy-options d-flex flex-column gap-2">
                        @foreach ([['public', 'Public community', 'Anyone can find, view, and join this community.'], ['approval', 'Approval required', 'Anyone can find the community, but you approve new members.'], ['private', 'Private community', 'Only invited people can find and join this community.']] as [$value, $label, $help])
                            <label class="radio-card border border-secondary-subtle p-3 rounded bg-light bg-opacity-50 d-flex align-items-start gap-3" style="cursor: pointer;">
                                <input type="radio" name="visibility" value="{{ $value }}" @checked(old('visibility', $community->visibility ?? 'public') === $value) class="form-check-input mt-1">
                                <span>
                                    <strong class="d-block text-dark">{{ $label }}</strong>
                                    <small class="text-muted">{{ $help }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <!-- Section 04: Community Guidelines -->
                <section class="form-section card bg-white mb-4 p-4 border shadow-sm rounded-3">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-primary text-white rounded-circle p-2 me-2 fw-bold" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">04</span>
                        <h2 class="h5 fw-bold mb-0 text-dark">Community guidelines</h2>
                    </div>
                    <p class="text-muted small mb-4">Set expectations for positive and respectful participation.</p>

                    <div class="field w-100">
                        <label class="form-label fw-bold small text-dark">Welcome message and guidelines</label>
                        <textarea class="form-control border-secondary-subtle mt-1" name="guidelines" rows="5" placeholder="Share a welcome note, community values, or important rules...">{{ old('guidelines', ($editing ?? false) ? ($community->guidelines ?? '') : '') }}</textarea>
                    </div>
                </section>
            </div>

            <!-- Sidebar / Actions -->
            <aside class="create-summary card bg-white text-dark p-4 border shadow-sm rounded-3 mb-4">
                <div class="summary-card mb-3">
                    <p class="eyebrow text-primary small fw-bold mb-1">
                        {{ ($editing ?? false) ? 'BEFORE YOU SAVE' : 'BEFORE YOU CREATE' }}
                    </p>
                    <h3 class="h5 fw-bold text-dark mb-3">
                        {{ ($editing ?? false) ? 'Review your changes' : 'A few things to know' }}
                    </h3>
                    <ul class="list-unstyled small mb-3 text-secondary">
                        <li class="mb-2">
                            <span class="badge bg-primary text-white me-1">1</span> 
                            {{ ($editing ?? false) ? 'Updates are visible to all members immediately.' : 'You will become the community organizer.' }}
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-primary text-white me-1">2</span> 
                            {{ ($editing ?? false) ? 'Existing events and conversations are not affected.' : 'You can invite co-organizers later.' }}
                        </li>
                        <li>
                            <span class="badge bg-primary text-white me-1">3</span> 
                            Community settings can be changed anytime.
                        </li>
                    </ul>

                    @unless ($editing ?? false)
                        <div class="form-check border-top pt-3 mt-3">
                            <input class="form-check-input" required type="checkbox" id="terms">
                            <label class="form-check-label small text-secondary" for="terms">
                                I agree to the 
                                <button type="button" class="btn btn-link text-primary p-0 text-decoration-underline align-baseline shadow-none">Community Guidelines</button> and 
                                <button type="button" class="btn btn-link text-primary p-0 text-decoration-underline align-baseline shadow-none">Terms of Service</button>.
                            </label>
                        </div>
                    @endunless
                </div>

                <div class="create-actions d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                    <a class="btn btn-outline-secondary px-4" href="{{ route('community.index') }}">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 text-white fw-bold">
                        {{ ($editing ?? false) ? 'Save changes' : 'Create community' }}
                    </button>
                </div>
            </aside>
        </form>

        @if ($editing ?? false)
            <form action="{{ route('community.destroy', $community->id) }}" method="POST" class="mt-3"
                onsubmit="return confirm('このコミュニティを削除しますか？この操作は取り消せません。');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fa-solid fa-trash me-1"></i> Delete community
                </button>
            </form>
        @endif
    </div>
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