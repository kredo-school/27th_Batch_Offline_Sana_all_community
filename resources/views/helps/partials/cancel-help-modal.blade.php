<style>
    .event-info-label {
        color: #888780;
        font-size: 11px;
    }
</style>

<div class="modal fade" id="cancelHelpModal-{{ $help->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <form method="POST" action="{{ route('helps.cancel', $help) }}">
                @csrf
                @method('PATCH')

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        {{ __('Cancel the Help') }}
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger mb-3">
                        {{ __('Are you sure you want to delete this Help? This cannot be undone.') }}
                    </p>

                    <div class="mb-3">
                        <div class="event-info-label">
                            {{ __('Help title') }}
                        </div>

                        <div class="event-info-value">
                            {{ $help->title }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="event-info-label">
                            {{ __('Date') }}
                        </div>

                        <div class="event-info-value">
                            {{ $eventDateLabel ?: __('Not specified') }}
                        </div>
                    </div>

                    <label for="delete_reason-{{ $help->getKey() }}" class="event-info-label">
                        {{ __('Reason') }}
                    </label>

                    <textarea name="delete_reason" id="delete_reason-{{ $help->getKey() }}" rows="3" maxlength="500" required
                        class="form-control @error('delete_reason') is-invalid @enderror"
                        placeholder="{{ __('Please tell the applicants why the Help is deleted.') }}">{{ old('delete_reason') }}</textarea>

                    @error('delete_reason')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Back') }}
                    </button>

                    <button type="submit" class="btn btn-danger">
                        {{ __('Cancel the Help') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
