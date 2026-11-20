{{--
    イベントの掲載（カード）。コミュニティのページとプロフィールで同じものを出す
    - コミュニティのイベントなら、カードの上にどのコミュニティのイベントかを出す（showCommunity が true のとき）
    - 中止したイベントは「Cancelled」、終わったイベントは「Ended」の印を画像の左上に重ねる
--}}
@php
    $showCommunity = $showCommunity ?? true;
    $postStatus = match (true) {
        $event->status === 'cancelled' => 'cancelled',
        $event->status === 'published' && $event->start_at?->isPast() => 'ended',
        default => null,
    };
@endphp
@once
    <style>
        .event-post {
            min-width: 0;
        }

        .event-post-card {
            position: relative;
        }

        .event-post-community-line {
            height: 1.5rem;
            margin-bottom: 8px;
            line-height: 1.5rem;
        }

        /* どのコミュニティのイベントか（コミュニティのオレンジ） */
        .event-post-community {
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            color: #d96a1c;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .event-post-community:hover {
            color: #b8560f;
            text-decoration: underline;
        }

        /* 終わった・中止したイベントの印（カードの画像の左上） */
        .event-post-status {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 5;
            padding: 5px 12px;
            background: #f1f3f5;
            color: #495057;
            font-size: 0.75rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        .event-post-status--cancelled {
            background: #f8d7da;
            color: #842029;
        }
    </style>
@endonce
<div class="event-post">
    {{-- コミュニティ名の行は、コミュニティのイベントでなくても同じ高さを取って、隣のカードと高さをそろえる --}}
    @if ($showCommunity)
        <div class="event-post-community-line">
            @if ($event->community_id && $event->community)
                <a href="{{ route('community.show', $event->community_id) }}" class="event-post-community">
                    <i class="fa-solid fa-people-group me-1" aria-hidden="true"></i>{{ $event->community->name }}
                </a>
            @endif
        </div>
    @endif
    <div class="event-post-card">
        @if ($postStatus === 'cancelled')
            <span class="badge rounded-pill event-post-status event-post-status--cancelled">Cancelled</span>
        @elseif ($postStatus === 'ended')
            <span class="badge rounded-pill event-post-status">Ended</span>
        @endif
        @include('events.partials.card', ['event' => $event])
    </div>
</div>
