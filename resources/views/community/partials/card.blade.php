{{-- コミュニティカード（イベントカード events/partials/card.blade.php と同じ構成・クラス名） --}}
@php
    $isOwner = auth()->id() === $community->owner_id;
    $memberCount = $community->approved_members_count + ($community->owner_is_member ? 0 : 1);
    $visibility = [
        'public' => ['Public', 'status-info'],
        'approval' => ['Approval required', 'status-warning'],
        'private' => ['Private', 'status-closed'],
    ][$community->visibility] ?? ['Public', 'status-info'];
@endphp

<article class="event-card">
    <div class="event-card-image-wrapper">
        @if ($community->image)
            {{-- 写真全体を枠内に収める --}}
            <img src="{{ asset('storage/' . $community->image) }}" alt="{{ $community->name }} cover image"
                class="event-card-image community-card-image">
        @else
            <div class="community-card-placeholder">{{ strtoupper(substr($community->name, 0, 2)) }}</div>
        @endif

        @if ($isOwner)
            {{-- My Community（イベントと同じ ⋮ メニュー） --}}
            <div class="event-actions dropdown">
                <button type="button" class="event-menu-button" id="communityMenu{{ $community->id }}"
                    data-bs-toggle="dropdown" aria-expanded="false" aria-label="community-menu">
                    <span aria-hidden="true">⋮</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="communityMenu{{ $community->id }}">
                    <li>
                        <a href="{{ route('community.edit', $community->id) }}" class="dropdown-item">Edit</a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteCommunityModal{{ $community->id }}">
                            Delete
                        </button>
                    </li>
                </ul>
            </div>
        @endif
    </div>

    <div class="event-card-body">
        <div class="event-card-badges">
            @if ($community->category)
                <span class="badge event-category">{{ $community->category }}</span>
            @endif
            <span class="badge event-status {{ $visibility[1] }}">{{ $visibility[0] }}</span>
        </div>

        <div class="d-flex align-items-center gap-2 mb-2">
            <x-icon :community="$community" class="rounded-circle border flex-shrink-0" style="width: 28px; height: 28px; font-size: 11px;" />
            <h2 class="event-card-title mb-0 text-truncate">{{ $community->name }}</h2>
        </div>
        <p class="event-card-description">{{ $community->description }}</p>

        <div class="event-card-footer">
            <span>
                <i class="fa-solid fa-user-group me-1"></i>{{ $memberCount }} {{ $memberCount === 1 ? 'member' : 'members' }}
            </span>
            <a href="{{ route('community.show', $community->id) }}">View details &gt;</a>
        </div>
    </div>
</article>

@if ($isOwner)
    {{-- 削除確認モーダル（イベントと同じ形） --}}
    <div class="modal fade" id="deleteCommunityModal{{ $community->id }}" tabindex="-1"
        aria-labelledby="deleteCommunityModalLabel{{ $community->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCommunityModalLabel{{ $community->id }}">Delete community?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>"{{ $community->name }}"</strong>?</p>
                    <p class="text-danger mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('community.destroy', $community->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
