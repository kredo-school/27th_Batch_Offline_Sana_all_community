{{-- プロフィールのページの左サイドバー（コミュニティのサイドバーと同じ枠・大きさ。色はプロフィールの青） --}}
<style>
    .profile-layout-sidebar {
        min-height: 700px;
        padding: 16px 12px;
        background: #fff;
    }

    .profile-layout-sidebar .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 0;
        border: 0;
        background: transparent;
    }

    .profile-layout-sidebar .profile-nav-item {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 0;
        padding: 14px 20px;
        border-radius: 8px;
        color: #111;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.5;
        text-decoration: none;
    }

    .profile-layout-sidebar .profile-nav-item i {
        width: 18px;
        color: #526570;
        text-align: center;
    }

    .profile-layout-sidebar .profile-nav-item:hover {
        background: #eaf3f8;
        color: #236d89;
    }

    .profile-layout-sidebar .profile-nav-item.is-active {
        background: #deeff7;
        color: #236d89;
        font-weight: 700;
    }

    .profile-layout-sidebar .profile-nav-item:hover i,
    .profile-layout-sidebar .profile-nav-item.is-active i {
        color: inherit;
    }

    /* 共通レイアウトの中では、プロフィールの中身を 1 列にして container の幅に合わせる */
    .event-content-col .profile-page {
        display: block;
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 0;
    }

    /* スマホ幅ではメニューを横に並べて、本文の上に積む */
    @media (max-width: 767.98px) {
        .profile-layout-sidebar {
            min-height: 0;
            padding: 10px 12px;
        }

        .profile-layout-sidebar .profile-sidebar {
            flex-direction: row;
            gap: 6px;
            overflow-x: auto;
        }

        .profile-layout-sidebar .profile-nav-item {
            flex-shrink: 0;
            width: auto;
            padding: 10px 14px;
            gap: 8px;
        }
    }
</style>
<aside class="profile-layout-sidebar">
    @include('profile.partials.navigation')
</aside>
