@extends('layouts.app')

@section('title', __('Search').' · '.$keyword)

@section('content')
<style>
    .search-results-page { max-width: 1180px; margin: 0 auto; padding: 28px 16px 56px; }
    .search-results-heading { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 12px; margin-bottom: 28px; }
    .search-results-heading h1 { margin: 0; font-size: clamp(1.7rem, 3vw, 2.4rem); }
    .search-results-query { color: #64748b; margin: 6px 0 0; }
    .search-again { display: flex; gap: 8px; width: min(100%, 440px); }
    .search-again input { min-width: 0; flex: 1; }
    .search-result-section { margin-top: 30px; }
    .search-result-section h2 { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; font-size: 1.25rem; margin-bottom: 12px; }
    .search-result-count { color: #64748b; font-size: .85rem; font-weight: 400; }
    .search-result-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px; }
    .search-result-card { display: block; min-height: 100%; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; color: inherit; text-decoration: none; }
    .search-result-card:hover { border-color: #94a3b8; color: inherit; }
    .search-result-card h3 { margin: 0 0 6px; font-size: 1rem; }
    .search-result-card p { margin: 0; color: #64748b; font-size: .9rem; }
    .search-result-muted { color: #64748b; }
</style>

<div class="search-results-page">
    <header class="search-results-heading">
        <div>
            <h1>{{ __('Search results') }}</h1>
            <p class="search-results-query">{{ __('Results for') }} “{{ $keyword }}”</p>
        </div>
        <form class="search-again" method="GET" action="{{ route('search') }}">
            <label class="visually-hidden" for="results-search">{{ __('Search') }}</label>
            <input id="results-search" class="form-control" type="search" name="search" value="{{ $keyword }}" required>
            <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
        </form>
    </header>

    <section class="search-result-section" aria-labelledby="search-events-title">
        <h2 id="search-events-title">{{ __('Events') }} <span class="search-result-count">{{ $events->count() }}</span></h2>
        @if ($events->isNotEmpty())
            <div class="search-result-grid">
                @foreach ($events as $event)
                    <a class="search-result-card" href="{{ route('events.show', $event) }}">
                        <h3>{{ $event->title }}</h3>
                        <p>{{ $event->start_at?->format('M d, Y') }}{{ $event->location ? ' · '.$event->location : '' }}</p>
                    </a>
                @endforeach
            </div>
        @else
            <p class="search-result-muted">{{ __('No events found.') }}</p>
        @endif
    </section>

    <section class="search-result-section" aria-labelledby="search-communities-title">
        <h2 id="search-communities-title">{{ __('Communities') }} <span class="search-result-count">{{ $communities->count() }}</span></h2>
        @if ($communities->isNotEmpty())
            <div class="search-result-grid">
                @foreach ($communities as $community)
                    <a class="search-result-card" href="{{ route('community.show', $community) }}">
                        <h3>{{ $community->name }}</h3>
                        <p>{{ $community->category ? \App\Models\Community::getLocalizedCategoryName($community->category) : __('Community') }}</p>
                    </a>
                @endforeach
            </div>
        @else
            <p class="search-result-muted">{{ __('No communities found.') }}</p>
        @endif
    </section>

    <section class="search-result-section" aria-labelledby="search-users-title">
        <h2 id="search-users-title">{{ __('Users') }} <span class="search-result-count">{{ $users->count() }}</span></h2>
        @if ($users->isNotEmpty())
            <div class="search-result-grid">
                @foreach ($users as $user)
                    <a class="search-result-card" href="{{ route('profile.show', $user) }}">
                        <h3>{{ $user->name }}</h3>
                        <p>{{ '@'.$user->username }}</p>
                    </a>
                @endforeach
            </div>
        @else
            <p class="search-result-muted">{{ __('No users found.') }}</p>
        @endif
    </section>

    <section class="search-result-section" aria-labelledby="search-categories-title">
        <h2 id="search-categories-title">{{ __('Categories') }} <span class="search-result-count">{{ $categories->count() }}</span></h2>
        @if ($categories->isNotEmpty())
            <div class="search-result-grid">
                @foreach ($categories as $category)
                    <a class="search-result-card" href="{{ route('events.index', ['category' => $category->id]) }}">
                        <h3>{{ $category->localized_name }}</h3>
                        <p>{{ __('Browse events') }}</p>
                    </a>
                @endforeach
            </div>
        @else
            <p class="search-result-muted">{{ __('No categories found.') }}</p>
        @endif
    </section>
</div>
@endsection
