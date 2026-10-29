<style>
    /* カード全体のスタイル */
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

    /* 画像エリア */
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

    /* お気に入り（ハート）ボタン */
    .favorite-button {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 36px;
        height: 36px;
        border-radius: 0;
        border: none;
        background: transparent;
        box-shadow: none;
        text-decoration: none;
        backdrop-filter: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #9ca3af;
        transition: color 0.2s ease;
    }

    .favorite-button:hover,
    .favorite-button.active {
        color: #ec4899;
        background: transparent;
        box-shadow: none;
    }

    /* Owner Actions Button */
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
        background: transparent;

        color: #ffffff;
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);

        cursor: pointer;
    }

    .event-menu-button:hover,
    .event-menu-button:focus {
        background: rgba(0, 0, 0, 0.25);
        color: #ffffff;
        outline: none;
    }

    .event-actions .dropdown-menu {
        min-width: 120px;
        z-index: 30;
    }

    .event-actions .dropdown-item {
        font-size: 14px;
    }

    /* カード本文 */
    .event-card-body {
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    /* バッジ */
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

    .event-status {
        background: #fecdd3;
        color: #9f1239;
    }

    .event-status.status-today {
        background: #fda4af;
        color: #9f1239;
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

    /* タイトル & 説明文 */
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

    /* フッター（日付・リンク） */
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


    /* レスポンシブ対応 */
    @media (max-width: 1200px) {
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

<article class="event-card">
    <div class="event-card-image-wrapper">
        {{-- <img src="{{ asset('images/event-placeholder.jpeg') }}" alt="event" class="event-card-image"> --}}
        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="event-card-image">

        @if (auth()->check() && (int) $event->owner_id === (int) auth()->id())
            {{-- My Event --}}
            @include('events.partials.event-actions', [
                'event' => $event,
            ])
        @else
            {{-- Bookmark --}}
            @auth
                @php
                    $isBookmarked = (bool) ($event->is_bookmarked ?? false);
                @endphp

                <form method="POST"
                    action="{{ $isBookmarked ? route('bookmarks.destroy', $event) : route('bookmarks.store', $event) }}">
                    @csrf

                    @if ($isBookmarked)
                        @method('DELETE')
                    @endif

                    <button type="submit" class="favorite-button {{ $isBookmarked ? 'active' : '' }}"
                        aria-label="{{ $isBookmarked ? 'Remove from bookmarks' : 'Add to bookmarks' }}"
                        aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}">
                        {{ $isBookmarked ? '♥' : '♡' }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="favorite-button" aria-label="Please log in to bookmark this">
                    ♡
                </a>
            @endauth
        @endif
    </div>

    <div class="event-card-body">
        <div class="event-card-badges">
            @foreach ($event->categories as $event_category)
                <span class="badge event-category">{{ $event_category->name }}</span>
            @endforeach

            @php
                $joinTillBadge = $event->join_till_badge;
            @endphp

            <span class="badge event-status {{ $joinTillBadge['class'] }}">
                {{ $joinTillBadge['label'] }}
            </span>
        </div>
        <h2 class="event-card-title">{{ $event->title }}</h2>
        <p class="event-card-description">{{ $event->description }}</p>
        <div class="event-card-footer">
            <time datetime="{{ $event->start_at?->format('Y-m-d') ?? '' }}">
                Held on
                {{ $event->start_at?->format('M d, Y') ?? 'Date TBD' }}
            </time>
            <a href="{{ route('events.show', $event->id) }}">View details &gt;</a>
        </div>
    </div>
</article>

@if (auth()->check() && (int) $event->owner_id === (int) auth()->id())
    @include('events.partials.delete-event-modal', [
        'event' => $event,
    ])
@endif
