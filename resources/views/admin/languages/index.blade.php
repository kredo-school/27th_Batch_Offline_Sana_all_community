@extends('layouts.app')

@section('title', __('Language words'))

@section('content')
<style>
    .admin-dashboard {
        --admin-ink: #173d3d;
        --admin-muted: #647b79;
        --admin-line: #d8e7e4;
        display: grid;
        grid-template-columns: 210px minmax(0, 1fr);
        min-height: calc(100vh - 70px);
        color: var(--admin-ink);
        background: #f5f8f7;
    }

    .admin-dashboard .admin-sidebar {
        position: fixed;
        inset-inline-start: 0;
        top: 70px;
        z-index: 100;
        width: 210px;
        height: calc(100vh - 70px);
        overflow-y: auto;
        padding: 24px 14px;
        border-inline-end: 1px solid var(--admin-line);
        background: #fff;
    }

    .admin-dashboard .admin-sidebar-title {
        margin: 0 10px 16px;
        color: var(--admin-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .admin-dashboard .admin-nav {
        display: grid;
        gap: 4px;
    }

    .admin-dashboard .admin-nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        min-height: 40px;
        padding: 0 11px;
        border-radius: 5px;
        color: var(--admin-ink);
        font-size: 14px;
        text-decoration: none;
    }

    .admin-dashboard .admin-nav-link i {
        width: 17px;
        color: #668380;
        text-align: center;
    }

    .admin-dashboard .admin-nav-link.active,
    .admin-dashboard .admin-nav-link:hover {
        color: #126b6b;
        background: #d9f2ef;
    }

    .admin-main {
        grid-column: 2;
        min-width: 0;
        padding: 28px clamp(16px, 3vw, 42px) 36px;
    }

    footer {
        position: relative;
        z-index: 101;
    }

    .admin-toolbar {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 14px;
        padding: 12px;
        border: 1px solid var(--admin-line);
        border-radius: 6px;
        background: #fff;
    }

    .admin-toolbar select {
        flex: 0 1 220px;
    }

    .admin-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--admin-line);
        border-radius: 5px;
        background: #fff;
    }

    .admin-table-wrap table {
        min-width: 680px;
    }

    .admin-table-wrap td,
    .admin-table-wrap th {
        vertical-align: middle;
    }

    @media (max-width: 760px) {
        .admin-dashboard {
            grid-template-columns: minmax(0, 1fr);
        }

        .admin-dashboard .admin-sidebar {
            position: static;
            width: auto;
            height: auto;
            overflow: visible;
            padding: 10px 12px;
            border-inline-end: 0;
            border-block-end: 1px solid var(--admin-line);
        }

        .admin-dashboard .admin-sidebar-title {
            margin-bottom: 8px;
        }

        .admin-dashboard .admin-nav {
            display: flex;
            overflow-x: auto;
        }

        .admin-dashboard .admin-nav-link {
            flex: 0 0 auto;
        }

        .admin-main {
            grid-column: 1;
        }

        .admin-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .admin-toolbar select {
            flex-basis: auto;
        }
    }
</style>
<div class="admin-dashboard">
    @include('admin.partials.sidebar')

    <section class="admin-main">
        @if (session('status'))
            <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
        @endif
        @if (session('translation_failures'))
            <div class="alert alert-warning" role="alert">
                <strong>{{ __('Some translations could not be completed.') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach (session('translation_failures') as $failedLocale)
                        <li>
                            {{ $locales[$failedLocale] ?? $failedLocale }}
                            ({{ $failedLocale }}) —
                            {{ __('The source text was saved instead.') }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($invalidTranslations !== [])
            <div class="alert alert-danger" role="alert">
                <strong>{{ __('Some translations appear to use the wrong language.') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($invalidTranslations as $invalidKey)
                        <li><code>{{ $invalidKey }}</code> — {{ __('Review this translation.') }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <p class="text-uppercase small fw-bold text-success mb-1">{{ __('Admin console') }}</p>
                <h1 class="h3 mb-0">{{ __('Language words') }}</h1>
                <p class="text-secondary mb-0">{{ $locales[$locale] }} ({{ $locale }})</p>
            </div>
            <a href="{{ route('admin.languages.create') }}" class="btn btn-success">
                <i class="fa-solid fa-plus me-1"></i>{{ __('Add word') }}
            </a>
        </div>

        <form method="GET" action="{{ route('admin.languages.index') }}" class="admin-toolbar">
            <div class="d-flex flex-wrap gap-2 flex-grow-1">
                <label class="visually-hidden" for="language-locale">{{ __('Language') }}</label>
                <select id="language-locale" name="locale" class="form-select" onchange="this.form.submit()">
                    @foreach ($locales as $code => $label)
                        <option value="{{ $code }}" @selected($locale === $code)>{{ $label }} ({{ $code }})</option>
                    @endforeach
                </select>
                <label class="visually-hidden" for="language-search">{{ __('Search') }}</label>
                <input id="language-search" type="search" name="search" value="{{ request('search') }}"
                    class="form-control" placeholder="{{ __('Search words') }}">
                <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th>{{ __('Word key') }}</th>
                        <th>{{ __('Translation') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($words as $key => $value)
                        <tr>
                            <td><code>{{ $key }}</code></td>
                            <td>{{ $value }}</td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route('admin.languages.edit', ['locale' => $locale, 'key' => $key]) }}"
                                        class="btn btn-outline-primary btn-sm"
                                        title="{{ __('Edit') }}"
                                        aria-label="{{ __('Edit :name', ['name' => $key]) }}">
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('admin.languages.destroy', ['locale' => $locale, 'key' => $key]) }}"
                                        method="POST" class="mb-0" data-confirm="true"
                                        data-confirm-action="{{ __('Delete') }}"
                                        data-confirm-message="{{ __('Delete this word?') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                            title="{{ __('Delete') }}"
                                            aria-label="{{ __('Delete :name', ['name' => $key]) }}">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-secondary py-4">{{ __('No words found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $words->links() }}
        </div>
    </section>
</div>
@endsection
