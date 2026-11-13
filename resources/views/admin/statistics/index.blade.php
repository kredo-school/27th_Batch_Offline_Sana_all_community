@extends('layouts.app')

@section('title', 'Admin Statistics')

@section('content')
    <style>
        .admin-dashboard {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: calc(100vh - 74px);
            color: #183b3a;
            background: #f5f8f7;
        }

        .admin-sidebar {
            padding: 24px 14px;
            border-right: 1px solid #dce8e5;
            background: #fff;
        }

        .admin-sidebar-title {
            margin: 0 10px 16px;
            color: #70827f;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-nav { display: grid; gap: 4px; }
        .admin-nav-link,
        .admin-nav-disabled {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 40px;
            padding: 0 11px;
            border-radius: 5px;
            color: #183b3a;
            font-size: 14px;
            text-decoration: none;
        }
        .admin-nav-link i,
        .admin-nav-disabled i { width: 17px; color: #668380; text-align: center; }
        .admin-nav-link:hover,
        .admin-nav-link.active { color: #126b6b; background: #d9f2ef; }
        .admin-nav-link.active { font-weight: 700; }
        .admin-nav-disabled { color: #98a9a7; }

        .admin-main { min-width: 0; padding: 28px clamp(16px, 3vw, 42px) 36px; }
        .admin-heading { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .admin-heading h1 { margin: 0; font-size: 25px; font-weight: 700; }
        .admin-eyebrow { margin: 0 0 5px; color: #668380; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .stats-card, .stats-panel { border: 1px solid #dce8e5; border-radius: 6px; background: #fff; box-shadow: 0 2px 8px rgb(22 66 62 / 5%); }
        .stats-card { padding: 18px; }
        .stats-card-label { color: #70827f; font-size: 13px; }
        .stats-card-value { margin-top: 7px; color: #126b6b; font-size: 28px; font-weight: 700; }
        .stats-panels { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
        .stats-panel { min-width: 0; overflow: hidden; }
        .stats-panel-header { padding: 13px 16px; border-bottom: 1px solid #dce8e5; font-weight: 700; }
        .stats-panel-body { padding: 12px 16px; }
        .stats-panel .table { margin-bottom: 0; }
        .stats-panel th { color: #70827f; font-size: 12px; white-space: nowrap; }
        .stats-url { max-width: 360px; overflow-wrap: anywhere; }
        .chart-bars { display: grid; grid-template-columns: repeat(7, minmax(24px, 1fr)); align-items: end; gap: 10px; min-height: 175px; padding-top: 12px; }
        .chart-column { display: grid; grid-template-rows: 18px 125px 24px; align-items: end; justify-items: center; gap: 4px; color: #70827f; font-size: 11px; }
        .chart-track { display: flex; width: 100%; height: 125px; align-items: end; justify-content: center; }
        .chart-bar { width: min(34px, 75%); min-height: 3px; border-radius: 4px 4px 0 0; background: #238f91; }
        .stats-empty { padding: 18px; color: #70827f; text-align: center; }

        @media (max-width: 900px) {
            .admin-dashboard { grid-template-columns: 1fr; }
            .admin-sidebar { border-right: 0; border-bottom: 1px solid #dce8e5; }
            .admin-nav { grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); }
        }
        @media (max-width: 680px) {
            .stats-panels { grid-template-columns: 1fr; }
            .admin-heading { align-items: start; }
        }
    </style>

    <div class="admin-dashboard">
        @include('admin.partials.sidebar')

        <main class="admin-main">
            <header class="admin-heading">
                <div>
                    <p class="text-uppercase small fw-bold text-success mb-1">Admin console</p>
                    <h1 class="mb-0 fw-medium">Statistics</h1>
                </div>
            </header>

            <section class="stats-grid" aria-label="Summary statistics">
                <article class="stats-card">
                    <div class="stats-card-label">Total page views</div>
                    <div class="stats-card-value">{{ number_format($totalPageViews) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">Guest page views</div>
                    <div class="stats-card-value">{{ number_format($guestPageViews) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">Logged-in page viewers</div>
                    <div class="stats-card-value">{{ number_format($uniquePageViewUsers) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">Category view events</div>
                    <div class="stats-card-value">{{ number_format($totalCategoryViews) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">Categories viewed</div>
                    <div class="stats-card-value">{{ number_format($trackedCategories) }}</div>
                </article>
            </section>

            <section class="stats-panel mb-3">
                <div class="stats-panel-header">Category views — last 7 days</div>
                <div class="stats-panel-body">
                    <div class="chart-bars">
                        @foreach ($dailyCategoryViews as $day)
                            <div class="chart-column" title="{{ number_format($day->view_count) }} views">
                                <span>{{ $day->view_count }}</span>
                                <div class="chart-track">
                                    <div class="chart-bar" style="height: {{ max(3, ($day->view_count / $maxDailyCategoryViews) * 100) }}%"></div>
                                </div>
                                <small>{{ \Carbon\Carbon::parse($day->date)->format('m/d') }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="stats-panels">
                <section class="stats-panel">
                    <div class="stats-panel-header">Most viewed categories</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>Category</th><th>Views</th><th>Users</th></tr></thead>
                            <tbody>
                                @forelse ($topCategories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ number_format($category->view_count) }}</td>
                                        <td>{{ number_format($category->unique_users) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="stats-empty">No category view data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="stats-panel">
                    <div class="stats-panel-header">Most viewed pages</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>URL</th><th>Views</th><th>Last viewed</th></tr></thead>
                            <tbody>
                                @forelse ($topPages as $page)
                                    <tr>
                                        <td class="stats-url">{{ $page->url }}</td>
                                        <td>{{ number_format($page->view_count) }}</td>
                                        <td>{{ $page->last_viewed_at ? \Carbon\Carbon::parse($page->last_viewed_at)->format('Y-m-d H:i') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="stats-empty">No page view data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="stats-panels">
                <section class="stats-panel">
                    <div class="stats-panel-header">Most active users</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>User</th><th>Page views</th><th>Category views</th></tr></thead>
                            <tbody>
                                @forelse ($activeUsers as $activeUser)
                                    <tr>
                                        <td>{{ $activeUser->name }}<br><small class="text-muted">{{ $activeUser->email }}</small></td>
                                        <td>{{ number_format($activeUser->page_view_count) }}</td>
                                        <td>{{ number_format($activeUser->category_view_count) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="stats-empty">No user activity data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="stats-panel">
                    <div class="stats-panel-header">Recent page views</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>URL</th><th>Visitor</th><th>IP address</th><th>Date</th></tr></thead>
                            <tbody>
                                @forelse ($recentPageViews as $pageView)
                                    <tr>
                                        <td class="stats-url">{{ $pageView->url }}</td>
                                        <td>{{ $pageView->user_name ?? 'Guest' }}</td>
                                        <td><code>{{ $pageView->ip_address }}</code></td>
                                        <td>{{ \Carbon\Carbon::parse($pageView->created_at)->format('m/d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="stats-empty">No page view data yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection
