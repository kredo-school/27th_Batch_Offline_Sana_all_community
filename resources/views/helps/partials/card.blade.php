<style>
    /* カード全体のスタイル */
    .help-card {
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

    .help-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* 画像エリア */
    .help-card-image-wrapper {
        position: relative;
        width: 100%;
        height: 160px;
        background-color: #f3f4f6;
    }

    .help-card-image {
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
    .help-actions {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 20;
    }

    .help-menu-button {
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

    .help-menu-button:hover,
    .help-menu-button:focus {
        background: rgba(0, 0, 0, 0.25);
        color: #ffffff;
        outline: none;
    }

    .help-actions .dropdown-menu {
        min-width: 120px;
        z-index: 30;
    }

    .help-actions .dropdown-item {
        font-size: 14px;
    }

    /* カード本文 */
    .help-card-body {
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    /* バッジ */
    .help-card-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .help-card .badge {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .help-category {
        background: #BEDB74;
        color: #365314;
    }

    .help-status {
        background: #fecdd3;
        color: #9f1239;
    }

    .help-status.status-today {
        background: #fda4af;
        color: #9f1239;
    }

    .help-status.status-urgent {
        background: #fde047;
        color: #713f12;
    }

    .help-status.status-info {
        background: #7dd3fc;
        color: #075985;
    }

    .help-status.status-closed {
        background: #e5e7eb;
        color: #4b5563;
    }

    /* タイトル & 説明文 */
    .help-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 8px 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        overflow-wrap: anywhere;
        height: 1.8em;
        flex-shrink: 0;
    }

    .help-card-description {
        font-size: 13px;
        color: #6b7280;
        margin: 0 0 16px 0;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        overflow-wrap: anywhere;
        height: 3em;
        flex-shrink: 0;
    }

    /* フッター（日付・リンク） */
    .help-card-footer {
        margin-top: auto;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: #6b7280;
    }

    .help-card-footer a {
        color: #111827;
        font-weight: 600;
        text-decoration: none;
    }



    /* レスポンシブ対応 */
    @media (max-width: 1200px) {
        .help-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .help-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $helpImageUrl = $help->image_path
        ? asset('storage/' . ltrim($help->image_path, '/'))
        : asset('images/onlylogo.jpg');

    $deadlineLabel = __('Open');
    $deadlineClass = 'status-info';

    if ($help->application_deadline) {
        if ($help->application_deadline->isBefore(today())) {
            $deadlineLabel = __('Closed');
            $deadlineClass = 'status-closed';
        } elseif (today()->diffInDays($help->application_deadline) <= 7) {
            $deadlineLabel = __('Closing soon');
            $deadlineClass = 'status-urgent';
        }
    }

    $eventDateLabel = null;

    if ($help->event_start_at) {
        $eventDateLabel = $help->event_start_at
            ->locale(app()->getLocale())
            ->isoFormat('lll');

        if ($help->event_end_at) {
            $eventDateLabel .= ' - ' . $help->event_end_at
                ->locale(app()->getLocale())
                ->isoFormat('lll');
        }
    }

    $rewardLabel = collect([$help->reward_type, $help->reward_amount])
        ->filter(fn($value) => filled($value))
        ->implode(' / ');
@endphp

<article class="help-card">
    {{-- Image --}}
    <div class="help-card-image-wrapper">
        <img src="{{ $helpImageUrl }}" alt="{{ $help->title }}" class="help-card-image">

        {{-- Help作成者本人：編集・削除メニュー --}}
        @if (auth()->check() && (int) $help->user_id === (int) auth()->id())
            @include('helps.partials.help-actions', [
                'help' => $help,
            ])

            {{-- ログイン済みの他ユーザー：ブックマーク --}}
        @elseif (auth()->check())
            @php
                $isBookmarked = (bool) ($help->is_bookmarked ?? false);
            @endphp

            <form method="POST"
                action="{{ $isBookmarked ? route('helps.bookmarks.destroy', $help) : route('helps.bookmarks.store', $help) }}">
                @csrf

                @if ($isBookmarked)
                    @method('DELETE')
                @endif

                <button type="submit" class="favorite-button {{ $isBookmarked ? 'active' : '' }}"
                    aria-label="{{ $isBookmarked ? __('Remove from bookmarks') : __('Add to bookmarks') }}"
                    aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}">
                    {{ $isBookmarked ? '♥' : '♡' }}
                </button>
            </form>

            {{-- 未ログインユーザー：ハートを表示し、ログイン画面へ遷移 --}}
        @else
            <a href="{{ route('login') }}" class="favorite-button" aria-label="{{ __('Log in to bookmark this help') }}">
                ♡
            </a>
        @endif
    </div>

    {{-- Card body --}}
    <div class="help-card-body">
        {{-- カテゴリ・募集者種別・ステータス --}}
        <div class="help-card-badges">
            @if ($help->category)
                <span class="badge help-category">
                    {{ $help->category->name }}
                </span>
            @endif

            @if ($help->requester_type)
                <span class="badge help-status">
                    {{ $help->requester_type }}
                </span>
            @endif

            @if ($help->status)
                <span class="badge help-status">
                    {{ $help->status }}
                </span>
            @endif

            <span class="badge help-status {{ $deadlineClass }}">
                {{ $deadlineLabel }}
            </span>
        </div>

        {{-- タイトル --}}
        <h2 class="help-card-title">
            {{ $help->title }}
        </h2>

        {{-- 会社名 --}}
        @if ($help->company_name)
            <p class="mb-2 fw-semibold text-dark">
                {{ $help->company_name }}
            </p>
        @endif

        {{-- 説明 --}}
        <p class="help-card-description">
            {{ \Illuminate\Support\Str::limit(strip_tags($help->description), 110) }}
        </p>

        {{-- Help情報 --}}
        <div class="small text-muted mb-3">
            @if ($eventDateLabel)
                <div class="mb-1">
                    <span aria-hidden="true">📅</span>
                    {{ $eventDateLabel }}
                </div>
            @endif

            @if ($help->location)
                <div class="mb-1">
                    <span aria-hidden="true">📍</span>
                    {{ $help->location }}
                </div>
            @endif

            @if ($rewardLabel)
                <div class="mb-1">
                    <span aria-hidden="true">🎁</span>
                    {{ $rewardLabel }}
                </div>
            @endif

            @if ($help->capacity)
                <div>
                    <span aria-hidden="true">👥</span>
                    @if ($help->capacity == 1)
                        {{ __('Capacity :count person', ['count' => $help->capacity]) }}
                    @else
                        {{ __('Capacity :count people', ['count' => $help->capacity]) }}
                    @endif
                </div>
            @endif
        </div>

        {{-- フッター --}}
        <div class="help-card-footer">
            <time datetime="{{ $help->application_deadline?->format('Y-m-d') ?? '' }}">
                @if ($help->application_deadline)
                    {{ __('Apply by') }}
                    {{ $help->application_deadline->locale(app()->getLocale())->isoFormat('LL') }}
                @else
                    {{ __('Open application') }}
                @endif
            </time>

            <a href="{{ route('helps.show', $help->id) }}">
                {{ __('View details') }} &gt;
            </a>
        </div>
    </div>
</article>

@if (auth()->check() && (int) $help->user_id === (int) auth()->id())
    @include('helps.partials.delete-help-modal', [
        'help' => $help,
    ])
@endif
