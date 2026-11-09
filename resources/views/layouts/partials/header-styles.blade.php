{{-- ホームとイベント／コミュニティで共通のヘッダー用スタイル --}}
<style>
        /* コミュニティのページにいる間、上の COMMUNITY をオレンジにする */
        .nav-link.community-nav-active {
            color: #F58634;
            font-weight: 700;
        }

        .navbar-avatar {
            width: 36px;
            height: 36px;
            object-fit: cover;
        }

        .message-chat-panel {
            position: fixed;
            right: 16px;
            bottom: 16px;
            z-index: 1080;
            display: flex;
            width: min(360px, calc(100vw - 32px));
            height: min(480px, calc(100vh - 32px));
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #cbd8d3;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 10px 36px #102c2540;
        }

        .message-chat-panel[hidden] {
            display: none;
        }

        .message-chat-header {
            display: flex;
            min-height: 58px;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #174e45;
            color: #fff;
        }

        .message-chat-header h2 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .message-chat-close {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            border: 0;
            border-radius: 4px;
            background: transparent;
            color: inherit;
        }

        .message-chat-close:hover,
        .message-chat-close:focus-visible {
            background: #ffffff24;
            
        }

        .message-chat-status {
            padding: 8px 14px 0;
            color: #68736f;
            font-size: 0.875rem;
        }

        .message-chat-list {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            margin: 0;
            padding: 12px;
            list-style: none;
        }

        .message-chat-form {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 10px;
            border-top: 1px solid #dce4e0;
        }

        .message-chat-form textarea {
            min-height: 40px;
            max-height: 112px;
            resize: vertical;
        }

        .message-chat-form button {
            flex: 0 0 42px;
            height: 40px;
        }

        .message-chat-item {
            max-width: 84%;
            align-self: flex-start;
            padding: 8px 10px;
            border-radius: 8px 8px 8px 2px;
            background: #f0f3f1;
            overflow-wrap: anywhere;
        }

        .message-chat-item--mine {
            align-self: flex-end;
            border-radius: 8px 8px 2px 8px;
            background: #e1f1e9;
        }

        .message-chat-sender {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 5px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .message-chat-avatar {
            display: grid;
            width: 26px;
            height: 26px;
            flex: 0 0 26px;
            place-items: center;
            overflow: hidden;
            border-radius: 50%;
            background: #dce9e3;
            color: #174e45;
            font-size: 0.75rem;
        }

        .message-chat-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .message-chat-item p {
            margin: 0;
            white-space: pre-wrap;
        }

        .message-chat-item time {
            display: block;
            margin-top: 4px;
            color: #68736f;
            font-size: 0.72rem;
        }

        .no-arrow::after {
        display: none !important;
        }

        .dropdown-icon-lg {
            font-size: 20px;
        }

        .nav-icons{
            width: 100px;
            height: auto;
        }

        .category-nav .nav-link:hover,
        .category-nav .nav-link:focus-visible {
           color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus-visible {
            color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .btn-create{
            width: 6%;
            height: 20px;
            text-align: center !important;
        }

        .btn-custom-outline:hover,
        .btn-custom-outline:active {
            color: #ffffff !important;
        }

        .user-menu-icon{
            width: 20px;
            height: auto;
        }

        @media (max-width: 480px) {
            .message-chat-panel {
                right: 10px;
                bottom: 10px;
                width: calc(100vw - 20px);
                height: min(60vh, 480px);
            }
        }
        .no-arrow::after {
        display: none !important;
        }

        .dropdown-icon-lg {
            font-size: 20px;
        }

        .navbar-icon-group {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .navbar-icon-group .nav-link {
            padding-right: 0.35rem;
            padding-left: 0.35rem;
        }

        .navbar-icon-group + .navbar-icon-group {
            margin-left: -0.35rem;
        }

        .nav-icons{
            width: 100px;
            height: auto;
        }

        .category-nav .nav-link:hover,
        .category-nav .nav-link:focus-visible {
           color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .category-nav {
            flex-wrap: wrap;
        }

        .category-nav .category-nav-link {
            white-space: nowrap;
        }

        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus-visible {
            color: #126b6b !important;
            background-color: rgba(192, 245, 245, 0.5) !important;
        }

        .btn-create{
            width: 6%;
            height: 20px;
            text-align: center !important;
        }

        .btn-custom-outline:hover,
        .btn-custom-outline:active {
            color: #ffffff !important;
        }

        .user-menu-icon{
            width: 20px;
            height: auto;
        }
        .all-navbar{
           
            left: 1px;
        }

        .header-list{

        }

</style>
