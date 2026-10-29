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
                    @if (session('contact_status'))
                        <div class="alert alert-success py-2" role="status">{{ session('contact_status') }}</div>
                    @endif
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('contact.store') }}">
                                            @csrf

                                            <div class="row mb-3">
                                                <label for="name" class="col-md-4 col-form-label text-md-start">{{ __('Name') }}</label>

                                                <div class="col-md-9">
                                                    <input id="name" type="text" class="form-control @error('name', 'contact') is-invalid @enderror" name="name" value="{{ old('name', optional(Auth::user())->name) }}" required autocomplete="name" autofocus>

                                                    @error('name', 'contact')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="username" class="col-md-4 col-form-label text-md-start">{{ __('Username') }}</label>

                                                <div class="col-md-9">
                                                    <input id="username" type="text" class="form-control @error('username', 'contact') is-invalid @enderror"
                                                        name="username" value="{{ old('username', optional(Auth::user())->username) }}" autocomplete="username">

                                                    @error('username', 'contact')
                                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                    @enderror
                                                </div>
                                            </div>


                                            <div class="row mb-3">
                                                <label for="email" class="col-md-4 col-form-label text-md-start">{{ __('Email Address') }}</label>

                                                <div class="col-md-9">
                                                    <input id="email" type="email" class="form-control @error('email', 'contact') is-invalid @enderror" name="email" value="{{ old('email', optional(Auth::user())->email) }}" required autocomplete="email">

                                                    @error('email', 'contact')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <label for="detail" class="col-md-4 col-form-label text-md-start">Detail</label>

                                                <div class="col-md-9">
                                                   <textarea class="form-control @error('detail', 'contact') is-invalid @enderror" id="detail" name="detail" rows="3" required>{{ old('detail') }}</textarea>
                                                    @error('detail', 'contact')
                                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                    @enderror
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

@if (session('contact_status') || $errors->contact->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var contactModal = document.getElementById('modal-contact');
            if (contactModal) {
                bootstrap.Modal.getOrCreateInstance(contactModal).show();
            }
        });
    </script>
@endif