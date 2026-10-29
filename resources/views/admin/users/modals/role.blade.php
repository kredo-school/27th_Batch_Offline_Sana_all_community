@php($newRole = $user->role === 'admin' ? 'user' : 'admin')

<div class="modal fade" id="change-role-user-{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="h5 modal-title">Change user role</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Change <span class="fw-bold">{{ $user->name }}</span>'s role to {{ $newRole }}?
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.users.role', $user->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="role" value="{{ $newRole }}">
                    <button type="button" class="btn btn-outline-warning btn-sm btn-custom-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</div>