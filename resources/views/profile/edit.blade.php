@extends('layouts.app')
@section('title', 'Edit Profile')
@section('content')
@php
    $imageUrl = fn ($path, $fallback) => $path
        ? (str_starts_with($path, 'https://') || str_starts_with($path, 'http://') ? $path : asset('storage/'.$path)) : $fallback;
    $cover = $imageUrl($user->profile?->cover_image, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=85');
    $avatar = $imageUrl($user->profile?->avatar, asset('images/onlylogo.jpg'));
@endphp
<div class="profile-page profile-editor">
    <nav class="profile-sidebar" aria-label="Profile navigation">
        @foreach (['Profile', 'Review', 'History', 'Event', 'Bookmark', 'Community'] as $tab)
            <a class="profile-nav-item {{ $loop->first ? 'is-active' : '' }}" href="{{ route('profile.show', $user->id) }}" @if($loop->first) aria-current="page" @endif>{{ $tab }}</a>
        @endforeach
    </nav>
    <div class="profile-editor-content">
        <div class="profile-editor-main">
            <h1>Edit Profile</h1>
            <p class="profile-editor-intro">Update your profile information. This information will be visible to other users.</p>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('profile.profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-edit-form">
                @csrf
                @method('PATCH')
                <div class="profile-editor-cover">
                    <img src="{{ $cover }}" alt="Profile cover" data-cover-preview>
                    <label class="profile-cover-button" for="cover_image">Change Cover</label>
                    <input class="visually-hidden" type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/gif">
                </div>
                <div class="profile-photo-field">
                    <label for="avatar">Photo profile</label>
                    <label class="profile-photo-picker" for="avatar" title="Change profile photo"><img src="{{ $avatar }}" alt="Change profile photo" data-avatar-preview><span><i class="fa-solid fa-camera" aria-hidden="true"></i></span></label>
                    <input class="visually-hidden" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                </div>
                <div class="profile-editor-fields">
                    <div><label for="name">Name *</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name"></div>
                    <div><label for="username">Username *</label><input id="username" name="username" value="{{ old('username', $user->username) }}" required maxlength="255" autocomplete="username"></div>
                    <div><label for="country">Nation</label><input id="country" name="country" value="{{ old('country', $user->profile?->country) }}" maxlength="100" autocomplete="country-name"></div>
                    <div><label for="preferred_language">Language *</label><select id="preferred_language" name="preferred_language" required>@foreach (['en' => 'English', 'ja' => 'Japanese'] as $code => $language)<option value="{{ $code }}" @selected(old('preferred_language', $user->preferred_language) === $code)>{{ $language }}</option>@endforeach</select></div>
                    <div class="profile-bio-field"><label for="bio">Bio</label><div class="profile-bio-input"><textarea id="bio" name="bio" maxlength="300" rows="3">{{ old('bio', $user->profile?->bio) }}</textarea><span id="bio-count" aria-live="polite">0/300</span></div></div>
                </div>
                <p class="profile-upload-feedback" role="status" aria-live="polite"></p>
                <div class="profile-editor-actions"><a href="{{ route('profile.show', $user->id) }}">Cancel</a><button type="submit">Save Changes</button></div>
            </form>
        </div>
        <aside class="profile-preview" aria-label="Profile preview">
            <h2>Profile Preview</h2><p class="profile-preview-caption">This is how your profile appears to other users</p>
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
