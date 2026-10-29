<style>
    .communication-sidebar .contact-sidebar{
        padding: 24px 14px;
        border-right: 1px solid #d8e7e4;
        background: #fff;
    }

    .communication-sidebar-title .contact-sidebar-title{
        margin: 0 10px 16px;
        color: #647b79;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .communication-sidebar-nav  .contact-sidebar-nav{
        display: grid;
        gap: 4px;
    }

    .communication-sidebar-link   .contact-sidebar-link{
        display: flex;
        min-height: 40px;
        align-items: center;
        gap: 11px;
        padding: 0 11px;
        border-radius: 5px;
        color: #173d3d;
        font-size: 14px;
        text-decoration: none;
    }

    .communication-sidebar-link i {
        width: 17px;
        color: #668380;
        text-align: center;
    }

    .contact-sidebar-link i {
        width: 17px;
        color: #668380;
        text-align: center;
    }

    .communication-sidebar-link:hover {
        color: #126b6b;
        background: #d9f2ef;
    }

     .contact-sidebar-link:hover {
        color: #126b6b;
        background: #d9f2ef;
    }

    @media (max-width: 760px) {
        .communication-sidebar .contact-sidebar{
            padding: 10px 12px;
            border-right: 0;
            border-bottom: 1px solid #d8e7e4;
        }

        .communication-sidebar-title .contact-sidebar-title{
            margin-bottom: 8px;
        }

        .communication-sidebar-nav .contact-sidebar-nav{
            display: flex;
            overflow-x: auto;
        }

        .communication-sidebar-link .contact-sidebar-link{
            flex: 0 0 auto;
        }
    }
</style>

<aside class="communication-sidebar">
    <p class="communication-sidebar-title">User Notification</p>
    <nav class="communication-sidebar-nav" aria-label="Community navigation">
        <a class="communication-sidebar-link {{ request()->routeIs('notifications.index') ? 'active' : '' }}"
            href="{{ route('notifications.index') }}" @if (request()->routeIs('notifications.index')) aria-current="page" @endif>
            <i class="fa-solid fa-bell" aria-hidden="true"></i>
            <span>Notifications</span>
        </a>
        <a class="communication-sidebar-link {{ request()->routeIs('messages.index') ? 'active' : '' }}"
            href="{{ route('messages.index') }}" @if (request()->routeIs('messages.index')) aria-current="page" @endif>
            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
            <span>Messages</span>
        </a>
        {{-- @if (Auth::user()->role === 'admin')
            <a class="communication-sidebar-link {{ request()->routeIs('admin.contacts*') ? 'active' : '' }}"
            href="{{ route('admin.contacts') }}" @if (request()->routeIs('admin.contacts*')) aria-current="page" @endif>
            <i class="fa-solid fa-user-group" aria-hidden="true"></i>
            <span>Contact From User</span>
        </a>
        @endif --}}
    </nav>
</aside>