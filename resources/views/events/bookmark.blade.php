@extends('layouts.app')

@section('title', 'Event Bookmark')

@section('content')
    <style>
        /* 全体の幅を持たせつつ、右側のスカスカ感をなくすコンパクト設計 */
        .events-main-wrapper {
            width: 100%;
            max-width: none;
            margin: 0 auto;
            min-width: 0;
            background-color: #F7F7F5
        }

        /* イベントカードの3列グリッド（適度な余白） */
        .event-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            width: 100%;
            box-sizing: border-box;
            margin-top: 24px;
        }

        /* カード全体のスタイル（完成イメージの上質な比率） */
        .event-card {
            background: #F7F7F5;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* 画像エリア（完成イメージに近い黄金比の高さ） */
        .event-card-image-wrapper {
            position: relative;
            width: 100%;
            height: 155px;
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
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s, background 0.2s;
        }

        .favorite-button:hover,
        .favorite-button.active {
            color: #ef5380;
            background: #ffffff;
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
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 11px;
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

        .event-actions {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 10;

            display: flex;
            gap: 8px;
        }

        .event-actions .btn {
            white-space: nowrap;
        }

        .no-events-container {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 0;
            width: 100%;
        }

        /* レスポンシブ対応 */
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

    <div class="container-fluid pe-0">
        <div class="row g-4">
            <aside class="col-12 col-lg-3 col-xl-2">
                @include('events.partials.sidebar')
            </aside>
            <div class="col-12 col-lg-9 col-xl-10">
                <div class="events-main-wrapper px-3 py-3 mx-0">
                    {{-- 検索・フィルター部分 --}}
                    @include('events.partials.filter')

                    <h1>Bookmark</h1>

                    {{-- イベント一覧グリッド --}}
                    <div class="event-grid">
                        @forelse ($events as $event)
                            @include('events.partials.card', ['event' => $event])
                        @empty
                            <p>Not bookmarked events.</p>
                        @endforelse
                    </div>

                    @if ($events instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">
                            {{ $events->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
