@extends('layouts.app')

@section('title', __('Community Chats'))

@section('sidebar', 'community')

@section('content')
    <style>
        .community-chats-page {
            min-height: calc(100vh - 74px);
            padding: 28px clamp(16px, 3vw, 42px) 36px;
            background: #f5f8f7;
        }

        .community-chats-header {
            max-width: 900px;
            margin: 0 auto 24px;
        }

        .community-chat-section {
            max-width: 900px;
            margin: 0 auto 28px;
        }

        .community-chat-section h2 {
            color: #173d3d;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .community-chat-card {
            margin-bottom: 14px;
            border: 1px solid #d8e7e4;
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 8px 20px #173d3d0d;
        }

        .community-chat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #e6efed;
        }

        .community-chat-card-header a,
        .community-chat-card-header summary {
            color: #173d3d;
            font-weight: 700;
            text-decoration: none;
        }

        .community-chat-card-header summary {
            cursor: pointer;
            list-style: none;
        }

        .community-chat-card-header summary::-webkit-details-marker {
            display: none;
        }

        /* コミュニティのページなので、ナビバーの Community と同じオレンジにする */
        .community-chat-card-header a:hover,
        .community-chat-card-header summary:hover {
            color: #F58634;
        }

        .community-chat-details {
            padding: 12px 16px;
            border-bottom: 1px solid #e6efed;
            color: #647b79;
            font-size: 0.85rem;
        }

        .community-chat-unread {
            background: #e3f2ec;
        }

        .community-chat-history {
            margin: 12px 16px 16px;
            border-top: 1px solid #edf2f0;
        }

        .community-chat-history summary {
            padding: 10px 0;
            color: #526866;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .community-chat-message {
            display: block;
            padding: 11px 16px;
            color: #405654;
            text-decoration: none;
        }

        .community-chat-message + .community-chat-message {
            border-top: 1px solid #edf2f0;
        }

        .community-chat-message:hover {
            background: #f5faf8;
        }

        .community-chat-message-author {
            color: #173d3d;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .community-chat-message time {
            color: #71807e;
            font-size: 0.75rem;
        }

        .community-chat-empty {
            padding: 16px;
            color: #71807e;
            font-size: 0.9rem;
        }
    </style>

    <main class="community-chats-page">
        <header class="community-chats-header">
            {{-- コミュニティのページなので、ナビバーの Community と同じオレンジにする --}}
            <p class="text-uppercase small fw-bold mb-1" style="color: #F58634;">{{ __('Communities') }}</p>
            <h1 class="h3 mb-2">{{ __('Community Chats') }}</h1>
            <p class="text-secondary small mb-0">{{ __('Recent messages from communities you own or have joined.') }}</p>
        </header>

        @foreach ([['key' => 'Owned Communities', 'communities' => $ownedCommunities], ['key' => 'Joined Communities', 'communities' => $joinedCommunities]] as $section)
            @php($sectionId = str($section['key'])->slug())
            <section class="community-chat-section" aria-labelledby="{{ $sectionId }}-title">
                <h2 id="{{ $sectionId }}-title">{{ __($section['key']) }}</h2>
                @forelse ($section['communities'] as $community)
                    <article class="community-chat-card">
                        <div class="community-chat-card-header">
                            <details class="w-100" @if ($community->unreadMessages->isNotEmpty()) open @endif>
                                <summary class="d-flex justify-content-between align-items-center gap-3">
                                    <span>{{ $community->name }}</span>
                                    @if ($community->unreadMessages->isNotEmpty())
                                        <span class="badge rounded-pill bg-danger">{{ __(':count unread', ['count' => $community->unreadMessages->count()]) }}</span>
                                    @else
                                        <span class="small text-muted">{{ __(':count recent messages', ['count' => $community->latestMessages->count()]) }}</span>
                                    @endif
                                </summary>
                                <div class="community-chat-details mt-3">
                                    <div class="d-flex flex-wrap gap-3">
                                        @if ($community->category)
                                            <span><strong>{{ __('Category') }}:</strong> {{ $community->localized_category }}</span>
                                        @endif
                                        @if ($community->location)
                                            <span><strong>{{ __('Location') }}:</strong> {{ $community->location }}</span>
                                        @endif
                                        <span><strong>{{ __('Access') }}:</strong> {{ $community->visibility_label }}</span>
                                    </div>
                                    @if ($community->description)
                                        <p class="mb-0 mt-2">{{ $community->description }}</p>
                                    @endif
                                </div>

                                @if ($community->unreadMessages->isNotEmpty())
                                    @foreach ($community->unreadMessages as $message)
                                        <a class="community-chat-message community-chat-unread" href="{{ route('community.show', $community->id) }}#chat">
                                            <span class="d-flex justify-content-between gap-3">
                                                <span class="community-chat-message-author">{{ $message->user?->username ?: __('Unknown user') }}</span>
                                                <time datetime="{{ $message->created_at?->toIso8601String() }}">{{ \App\Support\LocalizedValue::dateTime($message->created_at) }}</time>
                                            </span>
                                            <span class="d-block text-break">{{ $message->previewText() }}</span>
                                        </a>
                                    @endforeach
                                @endif

                                <details class="community-chat-history">
                                    <summary>{{ __('Chat history (:count)', ['count' => $community->latestMessages->count()]) }}</summary>
                                    @forelse ($community->latestMessages as $message)
                                        <a class="community-chat-message" href="{{ route('community.show', $community->id) }}#chat">
                                            <span class="d-flex justify-content-between gap-3">
                                                <span class="community-chat-message-author">{{ $message->user?->username ?: __('Unknown user') }}</span>
                                                <time datetime="{{ $message->created_at?->toIso8601String() }}">{{ \App\Support\LocalizedValue::dateTime($message->created_at) }}</time>
                                            </span>
                                            <span class="d-block text-break">{{ $message->previewText() }}</span>
                                        </a>
                                    @empty
                                        <p class="community-chat-empty mb-0">{{ __('No messages yet.') }}</p>
                                    @endforelse
                                </details>
                            </details>
                        </div>
                    </article>
                @empty
                    <p class="text-secondary small">{{ __('You have no :section.', ['section' => strtolower(__($section['key']))]) }}</p>
                @endforelse
            </section>
        @endforeach
    </main>
@endsection
