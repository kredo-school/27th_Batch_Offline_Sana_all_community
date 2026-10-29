@extends('layouts.app')

@section('title', 'Community')

@section('content')
<style>
    /* ホームページ (home.blade.php) と同じ配色・構成に合わせる */
    .community-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 340px;
        padding: 48px 16px;
        /* 画像内の文字と重ならないよう、人物と海が写る下側を表示する */
        background: url("{{ asset('images/BGforTop.png') }}") center bottom / cover no-repeat;
        text-align: center;
    }

    .community-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0.3) 100%);
    }

    .community-hero-inner {
        position: relative;
        width: min(100%, 560px);
    }

    .community-hero .search {
        display: flex;
    }

    .community-hero .search input {
        min-width: 0;
        flex: 1;
    }

    .btn-act {
        background-color: #A8CF45 !important;
    }

    .btn-com {
        background-color: #F58634 !important;
    }

    .community-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .community-section-title::before {
        content: '';
        width: 6px;
        height: 1.4em;
        border-radius: 3px;
        background: #F58634;
    }

    /* カバー写真がないときの枠の高さ */
    .community-cover-empty {
        height: 150px;
    }

    .community-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .community-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12) !important;
    }
</style>

<!-- ヒーロー（ホームページと同じく画像の上に検索とボタンを重ねる） -->
<section class="community-hero">
    <div class="community-hero-inner">
        <p class="text-white fw-bold small mb-1" style="letter-spacing: 0.2em;">SANA ALL</p>
        <h1 class="text-white fw-bold display-5 mb-2">COMMUNITY</h1>
        <p class="text-white mb-4">Find your people, join a community, and take part in local events.</p>

        <form class="mb-3" method="GET" action="{{ route('community.index') }}">
            <div class="search">
                <label class="visually-hidden" for="community-search">Search communities</label>
                <input class="form-control" id="community-search" type="search" name="search" value="{{ $keyword }}"
                    placeholder="Search communities">
                <button type="submit" aria-label="Search" title="Search" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass text-white"></i>
                </button>
            </div>
        </form>

        <a href="{{ route('community.create') }}" class="btn btn-com text-white fw-bold px-4 border">
            <i class="fa-solid fa-plus me-1"></i> CREATE COMMUNITY
        </a>
    </div>
</section>

<div class="container py-5">

    <!-- 成功メッセージ -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- コミュニティ一覧 -->
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="community-section-title h4 text-dark">
            {{ $keyword !== '' ? 'Results for "' . $keyword . '"' : 'Featured communities' }}
        </h2>
        @if ($keyword !== '')
            <a href="{{ route('community.index') }}" class="text-decoration-none small fw-bold mb-3">Clear search</a>
        @endif
    </div>

    <div class="row g-4 mb-5">
        @forelse ($communities as $community)
            <div class="col-md-6 col-lg-4">
                <div class="card community-card h-100 bg-white border shadow-sm rounded-3 overflow-hidden">
                    <!-- カバー写真：写真の縦横比に合わせて枠の高さが変わる -->
                    <div class="card-header border-0 position-relative p-0 {{ $community->image ? 'bg-white' : 'bg-light community-cover-empty' }}">
                        @if ($community->image)
                            <img src="{{ asset('storage/' . $community->image) }}" alt="{{ $community->name }} cover image" class="d-block w-100 object-fit-contain" style="max-height: 320px;">
                        @endif
                        <div class="position-absolute bottom-0 start-0 translate-middle-y ms-3 bg-white rounded-circle p-1 shadow">
                            <x-icon :community="$community" class="rounded-circle" style="width: 64px; height: 64px;" />
                        </div>
                        <span class="position-absolute top-0 end-0 m-2 badge bg-white text-dark border small">
                            <i class="fa-solid fa-user-group text-muted me-1"></i> {{ $community->approved_members_count + ($community->owner_is_member ? 0 : 1) }}
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="mt-3">
                            @if ($community->category)
                                <span class="badge btn-act text-white mb-2">{{ $community->category }}</span>
                            @endif
                        </div>
                        <h5 class="card-title fw-bold text-dark text-truncate">{{ $community->name }}</h5>
                        <p class="card-text text-muted small mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $community->description }}
                        </p>
                    </div>

                    <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center pb-3 pt-0">
                        <!-- 編集ボタンへのリンク（オーナーのみ） -->
                        @if (auth()->id() === $community->owner_id)
                            <a href="{{ route('community.edit', $community->id) }}" class="btn btn-outline-secondary btn-sm fw-bold">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('community.show', $community->id) }}" class="btn btn-primary btn-sm fw-bold ms-auto">
                            View details &gt;
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center border rounded-3 bg-white py-5 shadow-sm">
                    @if ($keyword !== '')
                        <p class="text-muted mb-0">No communities match "{{ $keyword }}".</p>
                    @else
                        <p class="text-muted mb-3">No communities found yet. Create your first community!</p>
                        <a href="{{ route('community.create') }}" class="btn btn-com text-white fw-bold">Create community</a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Upcoming Events -->
    <h2 class="community-section-title h4 text-dark">Upcoming events</h2>

    <div class="card bg-white border shadow-sm rounded-3 p-3">
        <div class="list-group list-group-flush">
            @forelse ($upcomingEvents as $event)
                <x-event-row :event="$event" show-community accent="#F58634" class="list-group-item py-3">
                    @if ($event->community)
                        <a href="{{ route('community.show', $event->community_id) }}" class="btn btn-outline-secondary btn-sm">Community</a>
                    @endif
                </x-event-row>
            @empty
                <p class="text-muted text-center py-3 mb-0">There are no upcoming community events yet.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection
