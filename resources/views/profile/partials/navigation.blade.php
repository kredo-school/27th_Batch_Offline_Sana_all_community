<nav class="profile-sidebar" aria-label="Personal navigation">
    @foreach (['feed' => ['Feed', 'fa-house'], 'event' => ['My Profile', 'fa-user'], 'bookmark' => ['Bookmarks', 'fa-bookmark'], 'history' => ['History', 'fa-clock-rotate-left']] as $tab => [$label, $icon])
        @php $selected = request()->routeIs('profile.show') && (int) $user->id === (int) auth()->id() && ($activeTab ?? '') === $tab; @endphp
        <a class="profile-nav-item {{ $selected ? 'is-active' : '' }}" href="{{ route('profile.show', ['id' => auth()->id(), 'tab' => $tab]) }}" @if($selected) aria-current="page" @endif><i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}</a>
    @endforeach
    @if (request()->routeIs('profile.edit'))
        <a class="profile-nav-item {{ request()->query('tab') !== 'blocked' ? 'is-active' : '' }}" href="{{ route('profile.edit') }}" @if(request()->query('tab') !== 'blocked') aria-current="page" @endif><i class="fa-solid fa-pen" aria-hidden="true"></i>Edit Profile</a>
        <a class="profile-nav-item {{ request()->query('tab') === 'blocked' ? 'is-active' : '' }}" href="{{ route('profile.edit', ['tab' => 'blocked']) }}" @if(request()->query('tab') === 'blocked') aria-current="page" @endif><i class="fa-solid fa-user-slash" aria-hidden="true"></i>Block list</a>
    @endif
</nav>
