@auth
    @php
        $isBookmarked = $isBookmarked ?? false;
    @endphp

    <form method="POST"
        action="{{ $isBookmarked ? route('bookmarks.destroy', $event) : route('bookmarks.store', $event) }}"
        class="bookmark-form">
        @csrf

        @if ($isBookmarked)
            @method('DELETE')
        @endif

        <button type="submit" class="bookmark-button {{ $isBookmarked ? 'is-bookmarked' : '' }}"
            aria-label="{{ $isBookmarked ? 'Remove from bookmarks' : 'Add to bookmarks' }}"
            aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}">
            <span aria-hidden="true">
                {{ $isBookmarked ? '♥' : '♡' }}
            </span>
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="bookmark-button" aria-label="Please log in to bookmark this">
        <span aria-hidden="true">♡</span>
    </a>
@endauth
