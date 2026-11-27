@php
    $modalId = $modalId ?? 'cancelEventModal-' . $event->id;
    $reasonId = $reasonId ?? 'cancel_reason-' . $event->id;

    $formatDateTime = function ($date, bool $includeDate = true): string {
        if (app()->getLocale() === 'ceb') {
            return $date->format($includeDate ? 'Y-m-d H:i' : 'H:i');
        }

        return $date->locale(app()->getLocale())->isoFormat($includeDate ? 'MMM D (ddd) HH:mm' : 'HH:mm');
    };

    $dateText =
        $formatDateTime($event->start_at) .
        ' ~ ' .
        ($event->start_at->isSameDay($event->end_at)
            ? $formatDateTime($event->end_at, false)
            : $formatDateTime($event->end_at));
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <form action="{{ route('events.cancel', $event) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="{{ $modalId }}Label">
                        {{ __('Cancel the event') }}
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>

                <div class="modal-body">
                    <p class="text-danger mb-3">
                        {{ __('Are you sure you want to cancel this event? This cannot be undone.') }}
                    </p>

                    <div class="mb-2">
                        <div class="event-info-label">
                            {{ __('Event name') }}
                        </div>
                        <div class="event-info-value">
                            {{ $event->title }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="event-info-label">
                            {{ __('Date') }}
                        </div>
                        <div class="event-info-value">
                            {{ $dateText }}
                        </div>
                    </div>

                    <label for="{{ $reasonId }}" class="event-info-label">
                        {{ __('Reason') }}
                    </label>

                    <textarea name="cancel_reason" id="{{ $reasonId }}" rows="3" maxlength="500" required
                        class="form-control @error('cancel_reason') is-invalid @enderror"
                        placeholder="{{ __('Please tell the participants why the event is cancelled.') }}">{{ old('cancel_reason') }}</textarea>

                    @error('cancel_reason')
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
                        {{ __('Cancel the event') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
