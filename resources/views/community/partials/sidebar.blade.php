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

    /* Chats の未読数 */
    .community-sidebar .sidebar-chat-link {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .community-sidebar .sidebar-chat-unread {
        background: #dc3545;
        color: #fff;
    }
</style>
@php
    $communityFilter = request()->routeIs('community.index') ? request('filter') : null;
    // まだ読んでいないコミュニティのチャット（ナビバーのコミュニティ通知と同じ数え方）
    $unreadChatCount = auth()->check()
        ? \App\Models\UserNotification::query()
            ->where('user_id', auth()->id())
            ->where('type', 'community_message')
            ->whereNull('read_at')
            ->count()
        : 0;
@endphp
<aside class="community-sidebar">
    {{-- Side Menu（コミュニティのページへのリンク） --}}
    <nav class="sidebar-menu">
        <a href="{{ route('community.index') }}"
            class="{{ request()->routeIs('community.index') && ! in_array($communityFilter, ['joined', 'owned'], true) ? 'active' : '' }}">
            {{ __('Top Page') }}
        </a>

        <a href="{{ route('community.index', ['filter' => 'joined']) }}"
            class="{{ $communityFilter === 'joined' ? 'active' : '' }}">
            {{ __('Joined Communities') }}
        </a>

        <a href="{{ route('community.index', ['filter' => 'owned']) }}"
            class="{{ $communityFilter === 'owned' ? 'active' : '' }}">
            {{ __('My Communities') }}
        </a>

        {{-- 参加中のコミュニティのチャットをまとめて見るページ --}}
        <a href="{{ route('community.chats') }}"
            class="sidebar-chat-link {{ request()->routeIs('community.chats') ? 'active' : '' }}">
            <span>Chats</span>
            @if ($unreadChatCount > 0)
                <span class="badge rounded-pill sidebar-chat-unread">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}<span class="visually-hidden"> unread messages</span></span>
            @endif
        </a>

        <a href="{{ route('community.create') }}"
            class="{{ request()->routeIs('community.create') ? 'active' : '' }}">
            {{ __('Create Community') }}
        </a>
    </nav>
</aside>
