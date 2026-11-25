@php
    $sort = $sort ?? request('sort', 'created_desc');
    $filterRouteName = $filterRouteName ?? 'helps.index';
@endphp

<style>
    .help-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .help-search-form {
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

    .help-action {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* アイコンクリック用の並び替えボタン（完成イメージ通り） */
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
        cursor: pointer;
        font-size: 18px;
        text-decoration: none;
    }

    .sort-dropdown-btn:hover {
        background: #F8F9FA;
    }

    .create-help-button {
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

    .category-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        width: 100%;
    }

    .category-button {
        padding: 8px 18px;
        background: #fff;
        border: 1px solid #DDDDDD;
        border-radius: 999px;
        color: #666;
        text-decoration: none;
        font-size: 13px;
        transition: all 0.2s;
    }

    .category-button:hover,
    .category-button.active {

        background: #000;

        border-color: #000;

        color: #fff;
    }
</style>

<section class="help-filters">
    {{-- Search Form --}}
    <form action="{{ route($filterRouteName) }}" method="GET" class="help-search-form">
        <div class="search-input-wrapper">
            <span class="search-icon">⌕</span>

            <input type="search" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="{{ __('Search helps') }}"
                aria-label="{{ __('Search helps') }}" @include('partials.search-suggest-attributes', ['type' => 'helps'])>
        </div>

        @if (request('category_id'))
            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
        @endif

        @if (request()->boolean('bookmarked'))
            <input type="hidden" name="bookmarked" value="1">
        @endif

        @if (request()->boolean('my_helps'))
            <input type="hidden" name="my_helps" value="1">
        @endif

        <input type="hidden" name="sort" value="{{ $sort }}">

        <button type="submit" class="btn btn-primary search-button">
            {{ __('Search') }}
        </button>
    </form>

    {{-- Sort & Create help Button --}}
    <div class="help-action">
        <div class="dropdown">
            <button class="sort-dropdown-btn" type="button" id="sortDropdownMenu" data-bs-toggle="dropdown"
                aria-expanded="false" aria-label="{{ __('Sort') }}">
                <i class="fa-solid fa-sort"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="sortDropdownMenu">
                <li>
                    <a class="dropdown-item {{ $sort === 'deadline_asc' ? 'active' : '' }}"
                        href="{{ route(
                            $filterRouteName,
                            array_merge(request()->except('page'), [
                                'sort' => 'deadline_asc',
                            ]),
                        ) }}">
                        {{ __('Deadline soonest') }}
                    </a>
                </li>

                <li>
                    <a class="dropdown-item {{ $sort === 'deadline_desc' ? 'active' : '' }}"
                        href="{{ route(
                            $filterRouteName,
                            array_merge(request()->except('page'), [
                                'sort' => 'deadline_desc',
                            ]),
                        ) }}">
                        {{ __('Deadline latest') }}
                    </a>
                </li>

                <li>
                    <a class="dropdown-item {{ $sort === 'created_desc' ? 'active' : '' }}"
                        href="{{ route(
                            'helps.index',
                            array_merge(request()->except('page'), [
                                'sort' => 'created_desc',
                            ]),
                        ) }}">
                        {{ __('Latest') }}
                    </a>
                </li>
            </ul>
        </div>

        @auth
            <a href="{{ route('helps.create') }}" class="btn btn-primary create-help-button">
                + {{ __('Create help') }}
            </a>
        @endauth
    </div>

    {{-- Category --}}
    <div class="category-list">
        <span class="w-100 small text-muted">
            {{ __('Category') }}
        </span>

        <a href="{{ route(
            'helps.index',
            array_merge(request()->except('page'), [
                'category_id' => null,
            ]),
        ) }}"
            class="category-button {{ !request('category_id') ? 'active' : '' }}">
            {{ __('All') }}
        </a>

        @foreach ($categories as $category)
            <a href="{{ route(
                'helps.index',
                array_merge(request()->except('page'), [
                    'category_id' => $category->id,
                ]),
            ) }}"
                class="category-button {{ (string) request('category_id') === (string) $category->id ? 'active' : '' }}">
                {{ __($category->name) }}
            </a>
        @endforeach
    </div>
</section>
