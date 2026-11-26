@extends('layouts.app')

@section('title', __('Admin') . ' ' . __('Statistics'))

@section('content')
    <style>
        .admin-dashboard {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: calc(100vh - 70px);
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

        .admin-dashboard .admin-sidebar {
            position: fixed;
            inset-inline-start: 0;
            top: 70px;
            width: 210px;
            height: calc(100vh - 70px);
            overflow-y: auto;
            z-index: 100;
            padding: 24px 14px;
            border-inline-end: 1px solid #dce8e5;
            background: #fff;
        }

        .admin-dashboard .admin-sidebar-title {
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

        .admin-main { grid-column: 2; min-width: 0; padding: 28px clamp(16px, 3vw, 42px) 36px; }
        .admin-heading { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .admin-heading h1 { margin: 0; font-size: 25px; font-weight: 700; }
        .admin-eyebrow { margin: 0 0 5px; color: #668380; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .stats-card, .stats-panel { border: 1px solid #dce8e5; border-radius: 6px; background: #fff; box-shadow: 0 2px 8px rgb(22 66 62 / 5%); }
        .stats-card { padding: 18px; }
        .stats-card-label { color: #70827f; font-size: 13px; }
        .stats-card-value { margin-top: 7px; color: #126b6b; font-size: 28px; font-weight: 700; }
        .stats-panels { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 18px; }
        .stats-chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 18px; }
        .stats-chart-toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .stats-period-control { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; color: #70827f; font-size: 13px; }
        .stats-panel { min-width: 0; overflow: hidden; }
        .stats-panel-header { padding: 13px 16px; border-bottom: 1px solid #dce8e5; font-weight: 700; }
        .stats-panel-body { padding: 12px 16px; }
        .stats-panel .table { margin-bottom: 0; }
        .stats-panel th { color: #70827f; font-size: 12px; white-space: nowrap; }
        .stats-url { max-width: 360px; overflow-wrap: anywhere; }
        .stats-line-chart { display: block; width: 100%; height: 170px; }
        .chart-grid-line { stroke: #e6efed; stroke-width: 1; }
        .chart-axis-label { fill: #70827f; font-size: 11px; }
        .chart-line { fill: none; stroke: #238f91; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .chart-point { fill: #126b6b; stroke: #fff; stroke-width: 2; }
        .chart-axis-labels { display: flex; justify-content: space-between; padding: 0 4px 4px 28px; color: #70827f; font-size: 11px; }
        .category-chart { display: grid; gap: 10px; padding: 14px 16px; }
        .category-chart-row { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(48px, 2fr) auto; align-items: center; gap: 9px; font-size: 12px; }
        .category-chart-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .category-chart-track { height: 10px; overflow: hidden; border-radius: 999px; background: #edf3f1; }
        .category-chart-bar { height: 100%; border-radius: inherit; background: #238f91; }
        .category-chart-value { color: #70827f; font-variant-numeric: tabular-nums; }
        .stats-empty { padding: 18px; color: #70827f; text-align: center; }

        @media (max-width: 900px) {
            .admin-dashboard { grid-template-columns: 1fr; }
            .admin-main { grid-column: 1; }
            .admin-dashboard .admin-sidebar {
                position: static;
                width: auto;
                height: auto;
                overflow: visible;
                z-index: auto;
                border-inline-end: 0;
                border-block-end: 1px solid #dce8e5;
            }
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
                    <p class="text-uppercase small fw-bold text-success mb-1">{{ __('Admin console') }}</p>
                    <h1 class="mb-0 fw-medium">{{ __('Statistics') }}</h1>
                </div>
            </header>

            <section class="stats-grid" aria-label="{{ __('Summary statistics') }}">
                <article class="stats-card">
                    <div class="stats-card-label">{{ __('Total page views') }}</div>
                    <div class="stats-card-value">{{ number_format($totalPageViews) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">{{ __('Guest page views') }}</div>
                    <div class="stats-card-value">{{ number_format($guestPageViews) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">{{ __('Logged-in page viewers') }}</div>
                    <div class="stats-card-value">{{ number_format($uniquePageViewUsers) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">{{ __('Category view events') }}</div>
                    <div class="stats-card-value">{{ number_format($totalCategoryViews) }}</div>
                </article>
                <article class="stats-card">
                    <div class="stats-card-label">{{ __('Categories viewed') }}</div>
                    <div class="stats-card-value">{{ number_format($trackedCategories) }}</div>
                </article>
            </section>

            <div class="stats-chart-toolbar">
                <div class="stats-period-control">
                    <span>{{ __('Period') }}</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="{{ __('Period') }}">
                        @foreach ($periodOptions as $periodKey => $periodLabel)
                            <a href="{{ route('admin.statistics', array_filter(['period' => $periodKey, 'detail' => $detail])) }}"
                                class="btn {{ $chartPeriod === $periodKey ? 'btn-success' : 'btn-outline-success' }}" style="width: 100px;"
                                @if ($chartPeriod === $periodKey) aria-current="page" @endif>
                                {{ __($periodLabel) }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <section class="stats-chart-grid">
                @foreach ($lineCharts as $chart)
                    <article class="stats-panel">
                        <div class="stats-panel-header">{{ __($chart->title) }}</div>
                        @include('admin.statistics.partials.line-chart', ['chart' => $chart])
                    </article>
                @endforeach
                <article class="stats-panel">
                    <div class="stats-panel-header">{{ __('Most viewed categories') }}</div>
                    <div class="category-chart">
                        @forelse ($topCategoryChart as $category)
                        <div class="category-chart-row" title="{{ \App\Support\LocalizedValue::category($category->name) }}: {{ __(':count views', ['count' => number_format($category->view_count)]) }}">
                            <span class="category-chart-name">{{ \App\Support\LocalizedValue::category($category->name) }}</span>
                                <div class="category-chart-track">
                                    <div class="category-chart-bar" style="width: {{ ($category->view_count / $maxCategoryChartCount) * 100 }}%"></div>
                                </div>
                                <span class="category-chart-value">{{ number_format($category->view_count) }}</span>
                            </div>
                        @empty
                            <div class="stats-empty">{{ __('No category view data yet.') }}</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <div class="stats-panels">
                <section class="stats-panel" id="top-categories">
                    <div class="stats-panel-header">{{ __('All-time most viewed categories') }}</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>{{ __('Category') }}</th><th>{{ __('Views') }}</th><th>{{ __('Users') }}</th></tr></thead>
                            <tbody>
                                @forelse ($topCategories as $category)
                                    <tr>
                                        <td>{{ \App\Support\LocalizedValue::category($category->name) }}</td>
                                        <td>{{ number_format($category->view_count) }}</td>
                                        <td>{{ number_format($category->unique_users) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="stats-empty">{{ __('No category view data yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($detail === 'categories')
                        <div class="d-flex justify-content-between align-items-center gap-2 p-3">
                            {{ $topCategories->links() }}
                            <a href="{{ route('admin.statistics', array_filter(['period' => $chartPeriod])) }}#top-categories">{{ __('Back') }}</a>
                        </div>
                    @elseif ($hasMore['categories'])
                        <div class="pb-3 px-3 text-end">
                            <a href="{{ route('admin.statistics', array_filter(['detail' => 'categories', 'period' => $chartPeriod])) }}#top-categories">{{ __('Show more') }}</a>
                        </div>
                    @endif
                </section>

                <section class="stats-panel" id="top-pages">
                    <div class="stats-panel-header">{{ __('Most viewed pages') }}</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>{{ __('URL') }}</th><th>{{ __('Views') }}</th><th>{{ __('Last viewed') }}</th></tr></thead>
                            <tbody>
                                @forelse ($topPages as $page)
                                    <tr>
                                        <td class="stats-url">{{ $page->url }}</td>
                                        <td>{{ number_format($page->view_count) }}</td>
                                        <td>{{ $page->last_viewed_at ? \App\Support\LocalizedValue::dateTime(\Carbon\Carbon::parse($page->last_viewed_at)) : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="stats-empty">{{ __('No page view data yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($detail === 'pages')
                        <div class="d-flex justify-content-between align-items-center gap-2 p-3">
                            {{ $topPages->links() }}
                            <a href="{{ route('admin.statistics', array_filter(['period' => $chartPeriod])) }}#top-pages">{{ __('Back') }}</a>
                        </div>
                    @elseif ($hasMore['pages'])
                        <div class="pb-3 px-3 text-end">
                            <a href="{{ route('admin.statistics', array_filter(['detail' => 'pages', 'period' => $chartPeriod])) }}#top-pages">{{ __('Show more') }}</a>
                        </div>
                    @endif
                </section>
            </div>

            <div class="stats-panels">
                <section class="stats-panel" id="active-users">
                    <div class="stats-panel-header">{{ __('Most active users') }}</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>{{ __('User') }}</th><th>{{ __('Page views') }}</th><th>{{ __('Category views') }}</th></tr></thead>
                            <tbody>
                                @forelse ($activeUsers as $activeUser)
                                    <tr>
                                        <td>{{ $activeUser->username ?: __('Unknown user') }}<br><small class="text-muted">{{ $activeUser->email }}</small></td>
                                        <td>{{ number_format($activeUser->page_view_count) }}</td>
                                        <td>{{ number_format($activeUser->category_view_count) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="stats-empty">{{ __('No user activity data yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($detail === 'active-users')
                        <div class="d-flex justify-content-between align-items-center gap-2 p-3">
                            {{ $activeUsers->links() }}
                            <a href="{{ route('admin.statistics', array_filter(['period' => $chartPeriod])) }}#active-users">{{ __('Back') }}</a>
                        </div>
                    @elseif ($hasMore['active-users'])
                        <div class="pb-3 px-3 text-end">
                            <a href="{{ route('admin.statistics', array_filter(['detail' => 'active-users', 'period' => $chartPeriod])) }}#active-users">{{ __('Show more') }}</a>
                        </div>
                    @endif
                </section>

                <section class="stats-panel" id="recent-page-views">
                    <div class="stats-panel-header">{{ __('Recent page views') }}</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>{{ __('URL') }}</th><th>{{ __('Visitor') }}</th><th>{{ __('IP address') }}</th><th>{{ __('Date') }}</th></tr></thead>
                            <tbody>
                                @forelse ($recentPageViews as $pageView)
                                    <tr>
                                        <td class="stats-url">{{ $pageView->url }}</td>
                                        <td>{{ $pageView->user_name ?? __('Guest') }}</td>
                                        <td><code>{{ $pageView->ip_address }}</code></td>
                                        <td>{{ \App\Support\LocalizedValue::dateTime(\Carbon\Carbon::parse($pageView->created_at)) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="stats-empty">{{ __('No page view data yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($detail === 'recent-page-views')
                        <div class="d-flex justify-content-between align-items-center gap-2 p-3">
                            {{ $recentPageViews->links() }}
                            <a href="{{ route('admin.statistics', array_filter(['period' => $chartPeriod])) }}#recent-page-views">{{ __('Back') }}</a>
                        </div>
                    @elseif ($hasMore['recent-page-views'])
                        <div class="pb-3 px-3 text-end">
                            <a href="{{ route('admin.statistics', array_filter(['detail' => 'recent-page-views', 'period' => $chartPeriod])) }}#recent-page-views">{{ __('Show more') }}</a>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection
