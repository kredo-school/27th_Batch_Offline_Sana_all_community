<style>
    .help-sidebar {
        width: 100%;
        min-height: 700px;
        background: #fff;
        box-sizing: border-box;
        position: sticky;
        top: 70px;
        align-self: flex-start;
        padding-left: 15px;
        padding-right: 15px;
    }

    .sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .sidebar-menu a {
        display: block;
        padding: 14px 20px;
        color: #111;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        border-radius: 8px;
    }

    .sidebar-menu a:hover,
    .sidebar-menu a.active {
        background: #d5eba1;
    }
</style>
<aside class="help-sidebar">

    {{-- Side Menu --}}
    <nav class="sidebar-menu">
        <a href="{{ route('helps.index') }}"
            class="{{ request()->routeIs('helps.index') &&
            !request()->boolean('registered') &&
            !request()->boolean('confirmed') &&
            !request()->boolean('bookmark') &&
            !request()->boolean('my_help')
                ? 'active'
                : '' }}">
            Top Page
        </a>


        <a href="{{ route('helps.index', ['bookmark' => 1]) }}"
            class="{{ request()->boolean('bookmark') ? 'active' : '' }}">
            Bookmark
        </a>

        <a href="{{ route('helps.index', ['registered' => 1]) }}"
            class="{{ request()->boolean('registered') ? 'active' : '' }}">
            Already Register
        </a>

        <a href="{{ route('helps.index', ['confirmed' => 1]) }}"
            class="{{ request()->boolean('confirmed') ? 'active' : '' }}">
            Confirmed help
        </a>

        @if ($hasMyhelps)
            <a href="{{ route('helps.index', ['my_help' => 1]) }}"
                class="{{ request()->boolean('my_help') ? 'active' : '' }}">
                My help
            </a>
        @endif
    </nav>
</aside>
