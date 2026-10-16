@props([
    'event', // イベントデータのオブジェクトまたは配列
])

<div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative">
    <!-- Bookmark -->
    <button type="button"
        class="btn p-0 position-absolute top-0 end-0 m-3 z-3 text-white bg-transparent border-0 bookmark-btn"
        aria-label="Bookmark">
        <i class="bi {{ $event['is_bookmarked'] ?? false ? 'bi-heart-fill text-danger' : 'bi-heart' }} fs-4"></i>
    </button>

    <!-- Image -->
    <div class="position-relative" style="height: 180px;">
        <img src="{{ $event['image_url'] ?? asset('images/default-event.jpg') }}" class="w-100 h-100 object-fit-cover"
            alt="{{ $event['title'] }}">
    </div>

    <div class="card-body d-flex flex-column p-4">
        <!-- Category & Status -->
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1 fw-semibold">
                {{ $event['category'] }}
            </span>
            @if (!empty($event['status_label']))
                <span
                    class="badge rounded-pill {{ $event['status_badge_class'] ?? 'bg-danger-subtle text-danger' }} px-3 py-1 fw-semibold">
                    {{ $event['status_label'] }}
                </span>
            @endif
            @if ($event['join_till'])

            @else ()



            @endif
        </div>

        <!-- Title -->
        <h5 class="card-title fw-bold text-dark mb-2 text-truncate">
            {{ $event['title'] }}
        </h5>

        <!-- Description -->
        <p class="card-text text-muted small mb-4 flex-grow-1">
            {{ $event['description'] }}
        </p>

        <!-- Footer -->
        <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
            <span class="text-secondary small">
                Held on {{ $event['held_date'] }}
            </span>
            <a href="{{ $event['url'] ?? '#' }}" class="text-dark fw-bold text-decoration-none small stretched-link">
                View details >
            </a>
        </div>
    </div>
</div>
