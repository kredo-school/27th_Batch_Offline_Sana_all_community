
<aside class="sidebar pe-lg-3">

    {{-- Calendar --}}

    {{-- Navigation area --}}
    <div class="nav flex-column gap-2">
        {{-- Top Page --}}
        <a href="#" class="nav-link rounded-3 px-3 py-2 fw-bold {{ request()->routeIs('events.index') ? 'bg-lime text-dark' : 'text-dark hover-bg-light' }}">
            Top Page
        </a>

        {{-- Already Register --}}
        <a href="#" class="nav-link rounded-3 px-3 py-2 fw-bold {{ request()->routeIs('events.registered') ? 'bg-lime text-dark' : 'text-dark hover-bg-light' }}">
            Already Register
        </a>

        {{-- Bookmark --}}
        <a href="#" class="nav-link rounded-3 px-3 py-2 fw-bold {{ request()->routeIs('events.bookmark') ? 'bg-lime text-dark' : 'text-dark hover-bg-light' }}">
            Bookmark
        </a>
    </div>
</aside>
