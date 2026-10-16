<div class="modal fade" id="change-password-{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-warning">
            <div class="modal-header border-warning">
                <h5 class="modal-title text-warning">
                    <i class="fa-solid fa-pen-to-square"></i>Change password
                </h5>
            </div>
            <div class="modal-body">
               <form action="{{ route('profile.change', $user->id) }}" method="post">
                    @csrf
                    @method('PATCH')

                    <label for="current_password" class="form-label">Enter your current password</label>
                    <input type="password" name="current_password" id="current_password" 
                           class="form-control @error('current_password') is-invalid @enderror" autofocus>
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="password" class="form-label mt-2">Enter your new password</label>
                    <input type="password" name="password" id="password" 
                           class="form-control @error('password') is-invalid @enderror">

                    <label for="password_confirmation" class="form-label mt-2">Confirm your new password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" 
                           class="form-control @error('password') is-invalid @enderror">

                    @error('password')
                        <div class="invalid-feedback d-block" role="alart">{{ $message }}</div>
                    @enderror
            </div>
            <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm text-white">Change</button>
                </form>
            </div>
        </div>
    </div>

</div>