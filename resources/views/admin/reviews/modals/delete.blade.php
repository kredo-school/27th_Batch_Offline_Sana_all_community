<div class="modal fade" id="delete-review-{{ $review->id}}">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>Delete review
                </h5>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this review?</p>
                <div class="mt-3">
                    <h6>{{ $review->title }}</h6>
                    <p class="mt-1 text-muted">{{ $review->body }}</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('review.destroy', $review->id) }}" method="post">
                    @csrf
                    @method('DELETE')

                    <button type="button"class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>

</div>