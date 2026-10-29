<style>
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

<section class="event-filters">
    {{-- Search Form --}}
    <form action="{{ route('events.index') }}" method="GET" class="event-search-form">
        <div class="search-input-wrapper">
            <span class="search-icon">⌕</span>
            <input type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Search events"
                aria-label="search-event">
        </div>

        @if (request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif

        <input type="hidden" name="sort" value="{{ $sort }}">

        <button type="submit" class="btn btn-primary search-button">
            Search

        </button>
    </form>

    {{-- Sort & Create event Button --}}
    <div class="event-action">
        <div class="dropdown">
            <button class="sort-dropdown-btn" type="button" id="sortDropdownMenu" data-bs-toggle="dropdown"
                aria-expanded="false" aria-label="sort-menu">
                <i class="fa-solid fa-sort"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="sortDropdownMenu">
                <li>
                    <a class="dropdown-item {{ $sort === 'date_asc' ? 'active' : '' }}"
                        href="{{ route('events.index', array_merge(request()->all(), [
                                'sort' => 'date_asc',
                            ]),
                        ) }}">
                        Nearest
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ $sort === 'date_desc' ? 'active' : '' }}"
                        href="{{ route('events.index', array_merge(request()->all(), [
                                'sort' => 'date_desc',
                            ]),
                        ) }}">
                        Furthest
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ $sort === 'created_desc' ? 'active' : '' }}"
                        href="{{ route('events.index', array_merge(request()->all(), [
                                'sort' => 'created_desc',
                            ]),
                        ) }}">
                        Latest
                    </a>
                </li>
            </ul>
        </div>

        <a href="{{ route('events.create') }}" class="btn btn-primary create-event-button">
            + Create event

        </a>
    </div>

    {{-- Chose Category --}}
    <div class="category-list">
        {{-- All --}}
        <a href="{{ route('events.index', [
            'category' => null,
            'keyword' => request('keyword'),
            'sort' => $sort,
        ]) }}"
            class="category-button {{ request('category', 'all') === 'all' ? 'active' : '' }}">
            All
        </a>

        {{-- Categories --}}
        @foreach ($categories as $value => $label)
            <a href="{{ route('events.index', [
                'category' => $value,
                'keyword' => request('keyword'),
                'sort' => request('sort'),
            ]) }}"
                class="category-button {{ (string) request('category') === (string) $value ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</section>
