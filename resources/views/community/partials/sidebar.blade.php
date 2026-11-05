<style>
    .community-sidebar {
        background: #fff;
        padding: 16px 12px;
        min-height: 700px;
    }

    .community-sidebar .sidebar-menu {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .community-sidebar .sidebar-menu a {
        display: block;
        padding: 14px 20px;
        color: #111;
        font-weight: 600;
        font-size: 15px;
        text-decoration: none;
        border-radius: 8px;
    }

    /* イベントは緑、コミュニティはオレンジ */
    .community-sidebar .sidebar-menu a:hover,
    .community-sidebar .sidebar-menu a.active {
        background: #fdd9bd;
    }
</style>
@php
    $communityFilter = request()->routeIs('community.index') ? request('filter') : null;
@endphp
<aside class="community-sidebar">
    {{-- Side Menu（コミュニティのページへのリンク） --}}
    <nav class="sidebar-menu">
        <a href="{{ route('community.index') }}"
            class="{{ request()->routeIs('community.index') && ! in_array($communityFilter, ['joined', 'owned'], true) ? 'active' : '' }}">
            Top Page
        </a>

        <a href="{{ route('community.index', ['filter' => 'joined']) }}"
            class="{{ $communityFilter === 'joined' ? 'active' : '' }}">
            Joined Communities
        </a>

        <a href="{{ route('community.index', ['filter' => 'owned']) }}"
            class="{{ $communityFilter === 'owned' ? 'active' : '' }}">
            My Communities
        </a>

        <a href="{{ route('community.create') }}"
            class="{{ request()->routeIs('community.create') ? 'active' : '' }}">
            Create Community
        </a>
    </nav>
</aside>
