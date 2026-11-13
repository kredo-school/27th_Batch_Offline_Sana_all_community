<aside class="admin-sidebar">
    <p class="admin-sidebar-title">{{ __('Administration') }}</p>
    <nav class="admin-nav" aria-label="{{ __('Admin sections') }}">
        <a class="admin-nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"
            href="{{ route('admin.users') }}" @if (request()->routeIs('admin.users')) aria-current="page" @endif>
            <i class="fa-solid fa-users"></i><span>{{ __('Users') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
            href="{{ route('admin.categories.index') }}" @if (request()->routeIs('admin.categories.*')) aria-current="page" @endif>
            <i class="fa-solid fa-tags"></i><span>{{ __('Categories') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.events') ? 'active' : '' }}"
            href="{{ route('admin.events') }}" @if (request()->routeIs('admin.events')) aria-current="page" @endif>
            <i class="fa-solid fa-calendar-days"></i><span>{{ __('Events') }}</span>
        </a>
        {{-- <a class="admin-nav-link {{ request()->routeIs('admin.jobs*') ? 'active' : '' }}"
            href="{{ route('admin.jobs') }}" @if (request()->routeIs('admin.jobs*')) aria-current="page" @endif>
            <i class="fa-solid fa-gears"></i><span>Queue Jobs</span>
        </a> --}}
        <a class="admin-nav-link {{ request()->routeIs('admin.communities') ? 'active' : '' }}"
            href="{{ route('admin.communities') }}" @if (request()->routeIs('admin.communities')) aria-current="page" @endif>
            <i class="fa-solid fa-users-rectangle"></i><span>{{ __('Communities') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.notifications') ? 'active' : '' }}"
            href="{{ route('admin.notifications') }}" @if (request()->routeIs('admin.notifications')) aria-current="page" @endif>
            <i class="fa-solid fa-bell"></i><span>{{ __('Notifications') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.messages') ? 'active' : '' }}"
            href="{{ route('admin.messages') }}" @if (request()->routeIs('admin.messages')) aria-current="page" @endif>
            <i class="fa-solid fa-envelope"></i><span>{{ __('Messages') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.comments') ? 'active' : '' }}"
            href="{{ route('admin.comments') }}" @if (request()->routeIs('admin.comments')) aria-current="page" @endif>
            <i class="fa-solid fa-comments"></i><span>{{ __('Comments') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.reviews') ? 'active' : '' }}"
            href="{{ route('admin.reviews') }}" @if (request()->routeIs('admin.reviews')) aria-current="page" @endif>
            <i class="fa-solid fa-star"></i><span>{{ __('Reviews') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.contacts') || request()->routeIs('contacts.*') ? 'active' : '' }}"
            href="{{ route('admin.contacts') }}" @if (request()->routeIs('admin.contacts') || request()->routeIs('contacts.*')) aria-current="page" @endif>
            <i class="fa-solid fa-inbox"></i><span>{{ __('Contact Us') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.statistics') || request()->routeIs('statistics.*') ? 'active' : '' }}"
            href="{{ route('admin.statistics') }}" @if (request()->routeIs('admin.statistics') || request()->routeIs('statistics.*')) aria-current="page" @endif>
            <i class="fa-solid fa-chart-line"></i><span>{{ __('Statistics') }}</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
            href="{{ route('admin.settings.edit') }}" @if (request()->routeIs('admin.settings.*')) aria-current="page" @endif>
            <i class="fa-solid fa-sliders"></i><span>{{ __('Site Settings') }}</span>
        </a>
    </nav>
</aside>