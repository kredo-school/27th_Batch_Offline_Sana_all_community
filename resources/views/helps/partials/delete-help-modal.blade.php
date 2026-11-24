@php
    $isHelpOwner = auth()->check() && (int) auth()->id() === (int) $help->user_id;

    $hasApplications = $help->applications()->exists();
@endphp

@if ($isHelpOwner)
    <div class="modal fade" id="deleteHelpModal{{ $help->id }}" tabindex="-1"
        aria-labelledby="deleteHelpModalLabel{{ $help->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteHelpModalLabel{{ $help->id }}">
                        {{ __('Delete help?') }}
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>

                <div class="modal-body">
                    <p>
                        {{ __('Are you sure you want to delete') }}
                        <strong>
                            "{{ $help->title }}"
                        </strong>?
                    </p>

                    <p class="text-danger mb-0">
                        {{ __('This action cannot be undone.') }}
                    </p>

                    @if ($hasApplications)
                        <p class="text-danger small mt-2 mb-0">
                            {{ __('This help has applications and cannot be deleted.') }}
                        </p>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>

                    <form method="POST"
                        action="{{ route('helps.destroy', [
                            'help' => $help->id,
                        ]) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger" @disabled($hasApplications)>
                            {{ __('Delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
