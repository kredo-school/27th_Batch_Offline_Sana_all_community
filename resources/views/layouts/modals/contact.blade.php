<div class="modal fade" id="modal-contact" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header border-success bg-warning py-1">
                    <h1 class="h1 modal-title text-white d-flex align-items-center w-100 justify-content-center ms-4 mb-0" id="modal-contact-title">
                        Contact Us
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form method="POST" action="#">
                                            @csrf

                                            <div class="row mb-3">
                                                <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>

                                                <div class="col-md-6">
                                                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>

                                                    @error('name')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="username" class="col-md-4 col-form-label text-md-end">{{ __('Username') }}</label>

                                                <div class="col-md-6">
                                                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                                                        name="username" value="{{ old('username') }}" required autocomplete="username">

                                                    @error('username')
                                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                    @enderror
                                                </div>
                                            </div>


                                            <div class="row mb-3">
                                                <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                                                <div class="col-md-6">
                                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                                    @error('email')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                                                <div class="col-md-6">
                                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                                    @error('password')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="detail" class="col-md-4 col-form-label text-md-end">Detail</label>

                                                <div class="col-md-6">
                                                   <textarea class="form-control" id="detail" name="detail" rows="3"></textarea>
                                                </div>
                                            </div>

                                            <div class="row mb-0">

                                                <div class="col-md-6  offset-md-3">
                                                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        Submit
                                                    </button>
                                                    
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>