@extends('layouts.app')
@section('title', 'Edit Profile')
@section('content')
@php
    $imageUrl = fn ($path, $fallback) => $path
        ? (str_starts_with($path, 'https://') || str_starts_with($path, 'http://') ? $path : asset('storage/'.$path)) : $fallback;
    $emptyCover = 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="340"></svg>');
    $defaultAvatar = asset('images/onlylogo.jpg');
    $cover = $imageUrl(old('remove_cover_image') ? null : $user->profile?->cover_image, $emptyCover);
    $avatar = $imageUrl(old('remove_avatar') ? null : $user->profile?->avatar, $defaultAvatar);
@endphp
<div class="profile-page profile-editor">
    @include('profile.partials.editor-sidebar')
    <div class="profile-editor-content">
        <header class="profile-editor-heading">
            <h1>Edit Profile</h1>
            <p class="profile-editor-intro">Update your profile information and choose who can see your location and occupation.</p>
        </header>
        <div class="profile-editor-main">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('profile.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-edit-form">
                @csrf
                @method('PATCH')
                <section class="profile-editor-section" aria-labelledby="profile-photos-heading">
                    <div class="profile-section-heading">
                        <h2 id="profile-photos-heading">Profile photos</h2>
                        <p id="profile-cover-help">Choose a photo, then adjust its position and zoom. Changes are applied when you save.</p>
                    </div>
                    <div class="profile-editor-cover">
                        <img src="{{ $cover }}" alt="Profile cover" data-cover-preview>
                        <label class="profile-cover-button" for="cover_image"><i class="fa-solid fa-camera" aria-hidden="true"></i> Change Cover</label>
                        <input class="visually-hidden" type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/gif" aria-describedby="profile-cover-help profile-image-help">
                    </div>
                    @include('profile.partials.image-controls', ['field' => 'cover_image', 'label' => 'cover', 'fallback' => $emptyCover])
                    <div class="profile-photo-field">
                        <div class="profile-photo-control">
                            <label class="profile-photo-picker" for="avatar" title="Change profile photo"><img src="{{ $avatar }}" alt="Change profile photo" data-avatar-preview><span><i class="fa-solid fa-camera" aria-hidden="true"></i></span></label>
                            <input class="visually-hidden" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif" aria-describedby="profile-image-help">
                        </div>
                        <div class="profile-photo-copy">
                            <label for="avatar">Profile photo</label>
                            <p id="profile-image-help">JPG, PNG or GIF. Maximum 6 MB per image. Animated GIFs use the first frame.</p>
                        </div>
                    </div>
                    @include('profile.partials.image-controls', ['field' => 'avatar', 'label' => 'photo', 'fallback' => $defaultAvatar])
                    <p class="profile-upload-feedback" role="status" aria-live="polite"></p>
                </section>
                <dialog class="profile-image-dialog" id="profile-image-dialog" aria-labelledby="profile-image-title" aria-describedby="profile-image-help-text">
                    <div class="profile-image-dialog-heading">
                        <h2 id="profile-image-title">Adjust photo</h2>
                        <button type="button" class="btn-close" data-crop-cancel aria-label="Close image adjustment"></button>
                    </div>
                    <p id="profile-image-help-text">Drag the image to reposition it, or use the sliders below.</p>
                    <div class="profile-crop-frame" data-crop-frame>
                        <canvas width="400" height="400" aria-label="Image crop preview"></canvas>
                    </div>
                    <div class="profile-crop-sliders">
                        <label for="profile-crop-zoom">Zoom</label><input type="range" id="profile-crop-zoom" min="1" max="3" step="0.05" value="1">
                        <label for="profile-crop-x">Horizontal position</label><input type="range" id="profile-crop-x" min="0" max="100" step="0.1" value="50">
                        <label for="profile-crop-y">Vertical position</label><input type="range" id="profile-crop-y" min="0" max="100" step="0.1" value="50">
                    </div>
                    <p class="profile-crop-feedback" role="status" aria-live="polite"></p>
                    <div class="profile-crop-actions">
                        <button type="button" data-crop-reset>Reset position</button>
                        <button type="button" data-crop-cancel>Cancel</button>
                        <button type="button" class="profile-crop-apply" data-crop-apply>Apply</button>
                    </div>
                </dialog>
                <section class="profile-editor-section" aria-labelledby="profile-info-heading">
                    <div class="profile-section-heading">
                        <h2 id="profile-info-heading">Profile information</h2>
                        <p>Fields marked with <span class="profile-required">*</span> are required.</p>
                    </div>
                    <div class="profile-editor-fields">
                        <div><label for="name">Name <span class="profile-required">*</span></label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name"></div>
                        <div><label for="username">Username <span class="profile-required">*</span></label><input id="username" name="username" value="{{ old('username', $user->username) }}" required maxlength="255" autocomplete="username"></div>
                        <div><label for="country">Country</label><input id="country" name="country" value="{{ old('country', $user->profile?->country) }}" maxlength="100" autocomplete="country-name"></div>
                        <div><label for="region">Region / City</label><input id="region" name="region" value="{{ old('region', $user->profile?->region) }}" maxlength="100" autocomplete="address-level1"></div>
                        <div><label for="occupation">Occupation</label><input id="occupation" name="occupation" value="{{ old('occupation', $user->profile?->occupation) }}" maxlength="100" autocomplete="organization-title"></div>
                        @php
                            $birthdayParts = explode('-', old('birthday', $user->profile?->birthday?->format('Y-m-d')) ?? '');
                            $birthdayYear = old('birthday_year', $birthdayParts[0] ?? '');
                            $birthdayMonth = old('birthday_month', $birthdayParts[1] ?? '');
                            $birthdayDay = old('birthday_day', $birthdayParts[2] ?? '');
                        @endphp
                        <div>
                            <label id="birthday-label">Birthday (optional)</label>
                            <div class="profile-birthday-fields" role="group" aria-labelledby="birthday-label" aria-describedby="birthday-help">
                                <select name="birthday_month" aria-label="Month" autocomplete="bday-month">
                                    <option value="">Month</option>
                                    @foreach (['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $loop->iteration }}" @selected((int) $birthdayMonth === $loop->iteration)>{{ $month }}</option>
                                    @endforeach
                                </select>
                                <select name="birthday_day" aria-label="Day" autocomplete="bday-day">
                                    <option value="">Day</option>
                                    @for ($day = 1; $day <= 31; $day++)
                                        <option value="{{ $day }}" @selected((int) $birthdayDay === $day)>{{ $day }}</option>
                                    @endfor
                                </select>
                                <select name="birthday_year" aria-label="Year" autocomplete="bday-year">
                                    <option value="">Year</option>
                                    @for ($year = now()->year; $year >= now()->year - 150; $year--)
                                        <option value="{{ $year }}" @selected((int) $birthdayYear === $year)>{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <small id="birthday-help">Your age is calculated from your birthday.</small>
                        </div>
                        <div><label for="gender">Gender (optional)</label><select id="gender" name="gender">@foreach (['' => 'Prefer not to say', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('gender', $user->profile?->gender) == $value)>{{ $label }}</option>@endforeach</select></div>
                        <div><label for="preferred_language">Language <span class="profile-required">*</span></label><select id="preferred_language" name="preferred_language" required>@foreach (['en' => 'English', 'ja' => 'Japanese'] as $code => $language)<option value="{{ $code }}" @selected(old('preferred_language', $user->preferred_language) === $code)>{{ $language }}</option>@endforeach</select></div>
                        <div class="profile-bio-field"><label for="bio">Bio</label><div class="profile-bio-input"><textarea id="bio" name="bio" maxlength="300" rows="3" aria-describedby="bio-count">{{ old('bio', $user->profile?->bio) }}</textarea><span id="bio-count" aria-live="polite">0/300</span></div></div>
                    </div>
                </section>
                <section class="profile-editor-section" aria-labelledby="profile-privacy-heading">
                    <div class="profile-section-heading">
                        <h2 id="profile-privacy-heading">Profile visibility</h2>
                        <p>Choose who can see your location and occupation. Your saved information is kept when you select Only me.</p>
                    </div>
                    <div class="profile-editor-fields">
                        <div>
                            <label for="show_location">Country and region / city</label>
                            <select id="show_location" name="show_location" aria-describedby="location-visibility-help">
                                <option value="1" @selected((string) old('show_location', (int) ($user->profile?->show_location ?? true)) === '1')>Everyone</option>
                                <option value="0" @selected((string) old('show_location', (int) ($user->profile?->show_location ?? true)) === '0')>Only me</option>
                            </select>
                            <small id="location-visibility-help">Applies to both country and region / city.</small>
                        </div>
                        <div>
                            <label for="show_occupation">Occupation</label>
                            <select id="show_occupation" name="show_occupation">
                                <option value="1" @selected((string) old('show_occupation', (int) ($user->profile?->show_occupation ?? true)) === '1')>Everyone</option>
                                <option value="0" @selected((string) old('show_occupation', (int) ($user->profile?->show_occupation ?? true)) === '0')>Only me</option>
                            </select>
                        </div>
                    </div>
                </section>
                <div class="profile-editor-actions"><a href="{{ route('profile.show', $user->id) }}">Cancel</a><button type="submit">Save Changes</button></div>
            </form>
        </div>
        <aside class="profile-preview profile-overview" aria-label="Profile preview">
            <header class="profile-preview-heading">
                <h2>Profile Preview</h2><p class="profile-preview-caption">See your changes as you edit.</p>
            </header>
            <section class="profile-card" aria-label="Your profile preview">
                <img class="profile-cover" src="{{ $cover }}" alt="Cover preview" data-cover-preview>
                <div class="profile-details">
                    <div class="profile-avatar-wrap">
                        <img class="profile-avatar" src="{{ $avatar }}" alt="Profile photo preview" data-avatar-preview>
                    </div>
                    <div class="profile-identity">
                        <h1 id="preview-name">{{ old('name', $user->name) }}</h1>
                        <p class="profile-handle" id="preview-username">{{ '@'.old('username', $user->username) }}</p>
                        <p class="profile-bio" id="preview-bio">{{ old('bio', $user->profile?->bio) ?: 'Welcome to my profile!' }}</p>
                    </div>
                    <dl class="profile-stats">@foreach (['Events Joined' => $user->attending_events_count, 'Events Hosted' => $user->events_owned_count, 'Followers' => $user->followers_count, 'Following' => $user->following_count] as $label => $count)<div><dt>{{ $label }}</dt><dd>{{ $count }}</dd></div>@endforeach</dl>
                </div>
            </section>
        </aside>
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('profile-edit-form');
    const bio = form.elements.bio;
    const update = () => {
        document.getElementById('preview-name').textContent = form.elements.name.value;
        document.getElementById('preview-username').textContent = '@' + form.elements.username.value;
        document.getElementById('preview-bio').textContent = bio.value || 'Welcome to my profile!';
        document.getElementById('bio-count').textContent = Array.from(bio.value).length + '/300';
    };
    form.addEventListener('input', update);
    update();
})();
</script>
<script src="{{ asset('js/profile-image-editor.js') }}" defer></script>
@endsection
