@extends('layouts.app')

@section('title', 'Community Chats')

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

        .community-chat-card-header a:hover,
        .community-chat-card-header summary:hover {
            color: #126b6b;
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
            <p class="text-uppercase small fw-bold text-success mb-1">Communities</p>
            <h1 class="h3 mb-2">Community Chats</h1>
            <p class="text-secondary small mb-0">Recent messages from communities you own or have joined.</p>
        </header>

        @foreach ([['title' => 'Owned Communities', 'communities' => $ownedCommunities], ['title' => 'Joined Communities', 'communities' => $joinedCommunities]] as $section)
            @php($sectionId = str($section['title'])->slug())
            <section class="community-chat-section" aria-labelledby="{{ $sectionId }}-title">
                <h2 id="{{ $sectionId }}-title">{{ $section['title'] }}</h2>
                @forelse ($section['communities'] as $community)
                    <article class="community-chat-card">
                        <div class="community-chat-card-header">
                            <details class="w-100" @if ($community->unreadMessages->isNotEmpty()) open @endif>
                                <summary class="d-flex justify-content-between align-items-center gap-3">
                                    <span>{{ $community->name }}</span>
                                    @if ($community->unreadMessages->isNotEmpty())
                                        <span class="badge rounded-pill bg-danger">{{ $community->unreadMessages->count() }} unread</span>
                                    @else
                                        <span class="small text-muted">{{ $community->latestMessages->count() }} recent messages</span>
                                    @endif
                                </summary>
                                <div class="community-chat-details mt-3">
                                    <div class="d-flex flex-wrap gap-3">
                                        <span><strong>Category:</strong> {{ $community->category }}</span>
                                        @if ($community->location)
                                            <span><strong>Location:</strong> {{ $community->location }}</span>
                                        @endif
                                        <span><strong>Access:</strong> {{ ucfirst($community->visibility) }}</span>
                                    </div>
                                    @if ($community->description)
                                        <p class="mb-0 mt-2">{{ $community->description }}</p>
                                    @endif
                                </div>

                                @if ($community->unreadMessages->isNotEmpty())
                                    @foreach ($community->unreadMessages as $message)
                                        <a class="community-chat-message community-chat-unread" href="{{ route('community.show', $community->id) }}#chat">
                                            <span class="d-flex justify-content-between gap-3">
                                                <span class="community-chat-message-author">{{ $message->user?->username ?: $message->user?->name ?: 'Unknown user' }}</span>
                                                <time datetime="{{ $message->created_at?->toIso8601String() }}">{{ $message->created_at?->format('M j, Y g:i A') }}</time>
                                            </span>
                                            <span class="d-block text-break">{{ $message->message }}</span>
                                        </a>
                                    @endforeach
                                @endif

                                <details class="community-chat-history">
                                    <summary>Chat history ({{ $community->latestMessages->count() }})</summary>
                                    @forelse ($community->latestMessages as $message)
                                        <a class="community-chat-message" href="{{ route('community.show', $community->id) }}#chat">
                                            <span class="d-flex justify-content-between gap-3">
                                                <span class="community-chat-message-author">{{ $message->user?->username ?: $message->user?->name ?: 'Unknown user' }}</span>
                                                <time datetime="{{ $message->created_at?->toIso8601String() }}">{{ $message->created_at?->format('M j, Y g:i A') }}</time>
                                            </span>
                                            <span class="d-block text-break">{{ $message->message }}</span>
                                        </a>
                                    @empty
                                        <p class="community-chat-empty mb-0">No messages yet.</p>
                                    @endforelse
                                </details>
                            </details>
                        </div>
                    </article>
                @empty
                    <p class="text-secondary small">You have no {{ strtolower($section['title']) }}.</p>
                @endforelse
            </section>
        @endforeach
    </main>
@endsection
