<div class="modal fade" id="edit-review-{{ $review->id }}" tabindex="-1" aria-labelledby="edit-review-title-{{ $review->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('review.update', $review->id) }}" method="post">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title" id="edit-review-title-{{ $review->id }}">Edit review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit-rating-{{ $review->id }}" class="form-label">Rating</label>
                    @php($selectedRating = (int) old('rating', $review->rating))
                    <select class="form-select" name="rating" id="edit-rating-{{ $review->id }}" required>
                        <option value="5" {{ $selectedRating === 5 ? 'selected' : '' }}>5 / 5 Very Good</option>
                        <option value="4" {{ $selectedRating === 4 ? 'selected' : '' }}>4 / 5 Good</option>
                        <option value="3" {{ $selectedRating === 3 ? 'selected' : '' }}>3 / 5 Average</option>
                        <option value="2" {{ $selectedRating === 2 ? 'selected' : '' }}>2 / 5 Bad</option>
                        <option value="1" {{ $selectedRating === 1 ? 'selected' : '' }}>1 / 5 Very Bad</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edit-title-{{ $review->id }}" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="edit-title-{{ $review->id }}" value="{{ $review->title }}" maxlength="150" required>
                </div>
                <div>
                    <label for="edit-body-{{ $review->id }}" class="form-label">Comments</label>
                    <textarea class="form-control" name="body" id="edit-body-{{ $review->id }}" rows="4" maxlength="1000" required>{{ $review->body }}</textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
</div>

