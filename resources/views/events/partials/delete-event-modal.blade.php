<div class="modal fade" id="deleteEventModal{{ $event->id }}" tabindex="-1"
    aria-labelledby="deleteEventModalLabel{{ $event->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEventModalLabel{{ $event->id }}">
                    Delete event?
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>
                    Are you sure you want to delete
                    <strong>"{{ $event->title }}"</strong>?
                </p>

                <p class="text-danger mb-0">
                    This action cannot be undone.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <form method="POST" action="{{ route('events.destroy', ['event' => $event->id]) }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
