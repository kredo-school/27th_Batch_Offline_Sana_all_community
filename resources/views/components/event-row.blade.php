{{-- イベント1行分（日付バッジ・タイトル・日時・場所）。スロットは右端のボタン等に使う --}}
@props(['event', 'showCommunity' => false, 'showDescription' => false, 'accent' => null])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center gap-3']) }}>
    <div class="text-center bg-light border rounded p-2 px-3">
        <span class="d-block fw-bold small {{ $accent ? '' : 'text-danger' }}" @if ($accent) style="color: {{ $accent }};" @endif>{{ $event->start_at->format('M') }}</span>
        <span class="d-block h5 fw-bold mb-0">{{ $event->start_at->format('d') }}</span>
    </div>
    <div>
        @if ($showCommunity && $event->community)
            <a href="{{ route('community.show', $event->community_id) }}" class="badge bg-light text-secondary border mb-1 text-decoration-none">{{ $event->community->name }}</a>
        @endif
        <h6 class="fw-bold mb-1 text-dark">{{ $event->title }}</h6>
        <small class="text-muted">
            <i class="fa-regular fa-clock me-1"></i>{{ $event->start_at->format('D, M j · g:i A') }}
            @if ($event->location)
                &nbsp;|&nbsp;<i class="fa-solid fa-location-dot me-1"></i>{{ $event->location }}
            @endif
        </small>
        @if ($showDescription && $event->description)
            <p class="text-muted small mb-0 mt-1">{{ $event->description }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="ms-auto">{{ $slot }}</div>
    @endif
</div>
