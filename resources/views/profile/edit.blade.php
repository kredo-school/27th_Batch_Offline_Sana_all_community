@extends('layouts.app')
@section('title', 'Edit Profile')
@section('content')
@php
    $imageUrl = fn ($path, $fallback) => $path
        ? (str_starts_with($path, 'https://') || str_starts_with($path, 'http://') ? $path : asset('storage/'.$path)) : $fallback;
    $cover = $imageUrl($user->profile?->cover_image, 'data:image/svg+xml,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="450"></svg>'));
    $avatar = $imageUrl($user->profile?->avatar, asset('images/onlylogo.jpg'));
@endphp
<div class="profile-page profile-editor">
    <nav class="profile-sidebar" aria-label="Profile navigation">
        @foreach (['Profile' => 'fa-user', 'Review' => 'fa-star', 'History' => 'fa-clock-rotate-left', 'Event' => 'fa-calendar-days', 'Bookmark' => 'fa-bookmark', 'Community' => 'fa-users'] as $tab => $icon)
            <a class="profile-nav-item {{ $loop->first ? 'is-active' : '' }}" href="{{ route('profile.show', $user->id) }}" @if($loop->first) aria-current="page" @endif><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $tab }}</a>
        @endforeach
    </nav>
    <div class="profile-editor-content">
        <header class="profile-editor-heading">
            <h1>Edit Profile</h1>
            <p class="profile-editor-intro">Update your profile information. This information will be visible to other users.</p>
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
                        <p id="profile-cover-help">Choose a wide cover photo. The image will be cropped to fit.</p>
                    </div>
                    <div class="profile-editor-cover">
                        <img src="{{ $cover }}" alt="Profile cover" data-cover-preview>
                        <label class="profile-cover-button" for="cover_image"><i class="fa-solid fa-camera" aria-hidden="true"></i> Change Cover</label>
                        <input class="visually-hidden" type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/gif" aria-describedby="profile-cover-help profile-image-help">
                    </div>
                    <div class="profile-photo-field">
                        <div class="profile-photo-control">
                            <label class="profile-photo-picker" for="avatar" title="Change profile photo"><img src="{{ $avatar }}" alt="Change profile photo" data-avatar-preview><span><i class="fa-solid fa-camera" aria-hidden="true"></i></span></label>
                            <input class="visually-hidden" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif" aria-describedby="profile-image-help">
                        </div>
                        <div class="profile-photo-copy">
                            <label for="avatar">Profile photo</label>
                            <p id="profile-image-help">JPG, PNG or GIF. Maximum 6 MB per image.</p>
                        </div>
                    </div>
                </section>
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
                <p class="profile-upload-feedback" role="status" aria-live="polite"></p>
                <div class="profile-editor-actions"><a href="{{ route('profile.show', $user->id) }}">Cancel</a><button type="submit">Save Changes</button></div>
            </form>
        </div>
        <aside class="profile-preview" aria-label="Profile preview">
            <h2>Profile Preview</h2><p class="profile-preview-caption">See your changes as you edit.</p>
            <img class="profile-preview-cover" src="{{ $cover }}" alt="Cover preview" data-cover-preview>
            <img class="profile-preview-avatar" src="{{ $avatar }}" alt="Profile photo preview" data-avatar-preview>
            <div class="profile-preview-text"><strong id="preview-name">{{ old('name', $user->name) }}</strong> <span id="preview-username">{{ '@'.old('username', $user->username) }}</span><p id="preview-bio">{{ old('bio', $user->profile?->bio) }}</p></div>
            <dl class="profile-preview-stats">@foreach (['Events Joined' => $user->attending_events_count, 'Events Hosted' => $user->events_owned_count, 'Followers' => $user->followers_count, 'Following' => $user->following_count] as $label => $count)<div><dt>{{ $label }}</dt><dd>{{ $count }}</dd></div>@endforeach</dl>
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
        document.getElementById('preview-bio').textContent = bio.value;
        document.getElementById('bio-count').textContent = Array.from(bio.value).length + '/300';
    };
    form.addEventListener('input', update);
    update();
    ['avatar', 'cover_image'].forEach(field => {
        let objectUrl;
        form.elements[field].addEventListener('change', event => {
            const file = event.target.files[0];
            if (!file) return;
            const feedback = document.querySelector('.profile-upload-feedback');
            if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type) || file.size > 6144 * 1024) {
                feedback.textContent = 'Choose a JPG, PNG or GIF image up to 6 MB.';
                event.target.value = '';
                return;
            }
            feedback.textContent = '';
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            document.querySelectorAll(field === 'avatar' ? '[data-avatar-preview]' : '[data-cover-preview]').forEach(image => image.src = objectUrl);
        });
    });
})();
</script>
@endsection
