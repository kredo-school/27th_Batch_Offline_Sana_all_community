<section aria-label="{{ __('Written reviews') }}">
    <div class="profile-hosted-header">
        <div class="profile-feed-heading"><h2>{{ __('Written reviews') }}</h2><span class="profile-history-count">{{ $writtenReviews->total() }} {{ __('reviews') }}</span></div>
        <nav class="profile-history-filters" aria-label="{{ __('Filter written reviews') }}">
            @foreach (['all' => 'All', 'event' => 'Events', 'host' => 'Hosts'] as $value => $label)
                <a href="{{ route('profile.show', ['id' => $user->id, 'tab' => 'review', 'review_type' => 'written', 'written_type' => $value]) }}#profile-activity" @if ($writtenType === $value) aria-current="page" @endif>{{ __($label) }}</a>
            @endforeach
        </nav>
    </div>
    @forelse ($writtenReviews as $review)
        <article class="profile-review-card">
            <header class="profile-review-header">
                <div class="profile-review-author">
                    <strong>{{ __($review->review_kind === 'host' ? 'Host review' : 'Event review') }}</strong>
                    @if ($review->review_kind === 'host' && $review->event?->owner)
                        <a class="profile-written-host" href="{{ route('profile.show', $review->event->owner_id) }}">{{ $review->event->owner->name }}</a>
                    @endif
                    @php($reviewDate = \Illuminate\Support\Carbon::parse($review->created_at))
                    <time datetime="{{ $reviewDate->toIso8601String() }}">{{ $reviewDate->locale(app()->getLocale())->isoFormat('LL') ?: $reviewDate->format('F j, Y') }}</time>
                </div>
                <div class="profile-review-rating" aria-label="{{ $review->rating }} {{ __('out of 5 stars') }}">
                    <span aria-hidden="true">{{ str_repeat('★', $review->rating) }}<span class="profile-review-unfilled">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                    <strong>{{ $review->rating }}<small>/5</small></strong>
                </div>
                <div class="dropdown profile-more-actions">
                    <button type="button" class="profile-more-button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Actions for :type review of :event', ['type' => $review->review_kind, 'event' => $review->event?->title ?? __('unavailable event')]) }}"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('profile.reviews.edit', ['type' => $review->review_kind, 'id' => $review->id, 'written_type' => $writtenType]) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i>{{ __('Edit review') }}</a>
                        <form method="POST" action="{{ route('profile.reviews.destroy', ['type' => $review->review_kind, 'id' => $review->id]) }}" onsubmit="return confirm(@json(__('Delete this review? This will remove its rating and comment.')));">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="written_type" value="{{ $writtenType }}">
                            <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i>{{ __('Delete review') }}</button>
                        </form>
                    </div>
                </div>
            </header>
            @if ($review->comment)
                <p class="profile-review-comment">{{ $review->comment }}</p>
            @else
                <p class="profile-review-no-comment">{{ __('Rating only') }}</p>
            @endif
            @if ($review->event)
                <a class="profile-review-event" href="{{ route('events.show', $review->event_id) }}">
                    <img src="{{ $imageUrl($review->event->image, asset('images/onlylogo.jpg')) }}" alt="" width="48" height="48" loading="lazy">
                    <span><small>{{ __('Reviewed event') }}</small><strong>{{ $review->event->title }}</strong></span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            @else
                <p class="profile-review-no-comment">{{ __('Event no longer available') }}</p>
            @endif
        </article>
    @empty
        <p class="profile-events-empty">{{ __('No written reviews yet.') }}</p>
    @endforelse
    {{ $writtenReviews->links() }}
</section>
