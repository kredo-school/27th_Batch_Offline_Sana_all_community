@php($newRole = $user->role === 'admin' ? 'user' : 'admin')

<div class="modal fade" id="change-role-user-{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="h5 modal-title">{{ __('Change user role') }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                {{ __('Change :name role to :role?', [
                    'name' => $user->name,
                    'role' => $newRole === 'admin' ? __('Admin') : __('User'),
                ]) }}
            </div>
            <div class="modal-footer">
                <form action="{{ route('admin.users.role', $user->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="role" value="{{ $newRole }}">
                    <button type="button" class="btn btn-outline-warning btn-sm btn-custom-outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Confirm') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>