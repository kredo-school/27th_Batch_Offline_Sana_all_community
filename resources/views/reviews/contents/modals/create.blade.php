<div class="modal fade" id="create-review-{{ $product->id }}" tabindex="-1" aria-labelledby="create-review-title-{{ $product->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('review.store', $product->id) }}" method="post">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="create-review-title-{{ $product->id }}">Write a review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="create-rating-{{ $product->id }}" class="form-label">Rating</label>
                    <select class="form-select" name="rating" id="create-rating-{{ $product->id }}" required>
                        <option value="" selected disabled>Choose a rating</option>
                        
                            <option value="5">5 / 5 Very Good</option>
                            <option value="4">4 / 5 Good</option>
                            <option value="3">3 / 5 Average</option>
                            <option value="2">2 / 5 Bad</option>
                            <option value="1">1 / 5 Very Bad</option>
                        
                    </select>
                </div>
                <div class="mb-3">
                    <label for="create-title-{{ $product->id }}" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" id="create-title-{{ $product->id }}" maxlength="150" required>
                </div>
                <div>
                    <label for="create-body-{{ $product->id }}" class="form-label">Comments</label>
                    <textarea class="form-control" name="body" id="create-body-{{ $product->id }}" rows="4" maxlength="1000" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit review</button>
            </div>
        </form>
    </div>
</div>
