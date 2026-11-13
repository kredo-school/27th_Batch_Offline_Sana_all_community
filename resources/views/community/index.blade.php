@extends('layouts.app')

@section('sidebar', 'community')

@section('title', 'Community')

@section('content')
<style>
    /* イベント一覧 (events/index.blade.php・events/partials/*) と同じ見た目に揃える */
    .events-main-wrapper {
        width: 100%;
        max-width: 1240px;
        margin: 0 auto;
        min-width: 0;
    }

    /* 検索・並び替え・作成ボタン・カテゴリー */
    .event-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .event-search-form {
        display: flex;
        flex: 1 1 350px;
        gap: 8px;
    }

    .search-input-wrapper {
        display: flex;
        align-items: center;
        flex: 1;
        background: #fff;
        border: 1px solid #DDDDDD;
        border-radius: 14px;
        padding: 0 16px;
    }

    .search-icon {
        color: #888;
        font-size: 22px;
        margin-right: 10px;
    }

    .search-input-wrapper input {
        width: 100%;
        height: 50px;
        border: 0;
        outline: 0;
        font-size: 14px;
        background: transparent;
    }

    .search-button {
        min-width: 90px;
        height: 50px;
        border-radius: 12px;
        font-weight: 600;
    }

    .event-action {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sort-dropdown-btn {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #DDDDDD;
        border-radius: 12px;
        color: #333;
        font-size: 18px;
    }

    .sort-dropdown-btn:hover {
        background: #F8F9FA;
    }

    .create-event-button {
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 24px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        white-space: nowrap;
    }

    /* カードの3列グリッド */
    .event-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 24px;
        width: 100%;
        margin-top: 24px;
    }

    .event-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        width: 100%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .event-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .event-card-image-wrapper {
        position: relative;
        width: 100%;
        height: 160px;
        background-color: #f3f4f6;
    }

    .event-card-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* コミュニティのカバーは切り抜かず全体を表示する */
    .community-card-image {
        object-fit: contain;
    }

    .community-card-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #9ca3af;
        font-size: 40px;
        font-weight: 700;
    }

    .event-actions {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 20;
    }

    .event-menu-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.25);
        color: #ffffff;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
        cursor: pointer;
    }

    .event-menu-button:hover,
    .event-menu-button:focus {
        background: rgba(0, 0, 0, 0.4);
        color: #ffffff;
        outline: none;
    }

    .event-actions .dropdown-menu {
        min-width: 120px;
        z-index: 30;
    }

    .event-card-body {
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .event-card-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .event-card .badge {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .event-category {
        background: #BEDB74;
        color: #365314;
    }

    .event-status.status-warning {
        background: #fde047;
        color: #713f12;
    }

    .event-status.status-info {
        background: #7dd3fc;
        color: #075985;
    }

    .event-status.status-closed {
        background: #e5e7eb;
        color: #4b5563;
    }

    .event-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px 0;
        line-height: 1.4;
    }

    .event-card-description {
        font-size: 13px;
        color: #6b7280;
        margin: 0 0 16px 0;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 38px;
    }

    .event-card-footer {
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #6b7280;
    }

    .event-card-footer a {
        color: #111827;
        font-weight: 600;
        text-decoration: none;
    }

    .no-events-container {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 0;
    }

    .community-section-heading {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 40px 0 0;
    }

    @media (max-width: 1100px) {
        .event-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .event-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="events-main-wrapper px-3 py-3 mx-0">

    {{-- 検索・並び替え・作成ボタン・カテゴリー（イベントの filter と同じ構成） --}}
    <section class="event-filters">
        <form action="{{ route('community.index') }}" method="GET" class="event-search-form">
            <div class="search-input-wrapper">
                <span class="search-icon">⌕</span>
                <input type="search" name="search" value="{{ $keyword }}" placeholder="{{ __('Search') }} {{ strtolower(__('Communities')) }}"
                    aria-label="search-community">
            </div>

            @if ($filter)
                <input type="hidden" name="filter" value="{{ $filter }}">
            @endif
            <input type="hidden" name="sort" value="{{ $sort }}">

            <button type="submit" class="btn btn-primary search-button">{{ __('Search') }}</button>
        </form>

        <div class="event-action">
            <div class="dropdown">
                <button class="sort-dropdown-btn" type="button" id="communitySortMenu" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="sort-menu">
                    <i class="fa-solid fa-sort"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="communitySortMenu">
                    @foreach (['latest' => 'Latest', 'oldest' => 'Oldest', 'name' => 'Name (A–Z)'] as $value => $label)
                        <li>
                            <a class="dropdown-item {{ $sort === $value ? 'active' : '' }}"
                                href="{{ route('community.index', array_merge(request()->except('page'), ['sort' => $value])) }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <a href="{{ route('community.create') }}" class="btn btn-primary create-event-button">+ {{ __('Create').' '.__('Community') }}</a>
        </div>
    </section>

    {{-- コミュニティ一覧グリッド --}}
    <div class="event-grid">
        @forelse ($communities as $community)
            @include('community.partials.card', ['community' => $community])
        @empty
            <div class="no-events-container">
                <h3 class="text-muted text-center mb-3">
                    {{ $keyword !== '' ? __('No communities match') . ' "' . $keyword . '"' : __('No communities yet') }}
                </h3>
                @if ($keyword === '' && $filter !== 'joined')
                    <a href="{{ route('community.create') }}" class="btn btn-primary">+ {{ __('Create').' '.__('Community') }}</a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- コミュニティのイベント --}}
    <h2 class="community-section-heading">{{ __('Upcoming community events') }}</h2>

    <div class="event-card mt-3">
        <div class="list-group list-group-flush">
            @forelse ($upcomingEvents as $event)
                <x-event-row :event="$event" show-community class="list-group-item py-3 px-4">
                    <a href="{{ route('events.show', $event->id) }}" class="btn btn-outline-secondary btn-sm">{{ __('View details') }} &gt;</a>
                </x-event-row>
            @empty
                <p class="text-muted text-center py-4 mb-0">{{ __('There are no upcoming community events yet.') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
