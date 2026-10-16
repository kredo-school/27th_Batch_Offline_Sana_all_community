<aside class="admin-sidebar">
    <p class="admin-sidebar-title">Administration</p>
    <nav class="admin-nav" aria-label="Admin sections">
        <a class="admin-nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}"
            href="{{ route('admin.users') }}" @if (request()->routeIs('admin.users')) aria-current="page" @endif>
            <i class="fa-solid fa-users"></i><span>Users</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
            href="{{ route('admin.categories.index') }}" @if (request()->routeIs('admin.categories.*')) aria-current="page" @endif>
            <i class="fa-solid fa-tags"></i><span>Categories</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.events') ? 'active' : '' }}"
            href="{{ route('admin.events') }}" @if (request()->routeIs('admin.events')) aria-current="page" @endif>
            <i class="fa-solid fa-calendar-days"></i><span>Events</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.communities') ? 'active' : '' }}"
            href="{{ route('admin.communities') }}" @if (request()->routeIs('admin.communities')) aria-current="page" @endif>
            <i class="fa-solid fa-users-rectangle"></i><span>Communities</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.notifications') ? 'active' : '' }}"
            href="{{ route('admin.notifications') }}" @if (request()->routeIs('admin.notifications')) aria-current="page" @endif>
            <i class="fa-solid fa-bell"></i><span>Notifications</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.comments') ? 'active' : '' }}"
            href="{{ route('admin.comments') }}" @if (request()->routeIs('admin.comments')) aria-current="page" @endif>
            <i class="fa-solid fa-comments"></i><span>Comments</span>
        </a>
        <a class="admin-nav-link {{ request()->routeIs('admin.reviews') ? 'active' : '' }}"
            href="{{ route('admin.reviews') }}" @if (request()->routeIs('admin.reviews')) aria-current="page" @endif>
            <i class="fa-solid fa-star"></i><span>Reviews</span>
        </a>
    </nav>
</aside>