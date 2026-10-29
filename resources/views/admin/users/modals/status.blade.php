@if ($user->trashed())
    {{-- activate --}}
    <div class="modal fade" id="activate-user-{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header border-success">
                    <h3 class="h5 modal-title text-success" id="activate-user-title-{{ $user->id }}">
                        <i class="fa-solid fa-user-check"></i> Activate User
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure want to Activate <span class="fw-bold">{{ $user->name }}</span>
                </div>
                <div class="modal-footer border-0">
                    <form action="{{ route('admin.users.activate', $user->id) }}" method="post">
                        @csrf
                        @method('PATCH')

                        <button type="button" class="btn btn-outline-warning btn-sm"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm">Activate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- deactivate --}}
    <div class="modal fade" id="deactivate-user-{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header border-danger">
                    <h3 class="h5 modal-title text-danger" id="deactivate-user-title-{{ $user->id }}">
                        <i class="fa-solid fa-user-slash"></i> Deactivate User
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure want to deactivate <span class="fw-bold">{{ $user->name }}</span>
                </div>
                <div class="modal-footer border-0">
                    <form action="{{ route('admin.users.deactivate', $user->id) }}" method="post">
                        @csrf
                        @method('DELETE')

                        <button type="button" class="btn btn-outline-warning btn-sm btn-custom-outline"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Deactivate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
