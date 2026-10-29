@extends('layouts.app')

@section('title', 'Contact Inquiry #' . $contact->id)

@section('content')
    <style>
        .show-section-layout {
            display: grid;
            grid-template-columns: 210px minmax(0, 1fr);
            min-height: calc(100vh - 74px);
            color: #173d3d;
            background: #f5f8f7;
        }

        .show-section-main {
            min-width: 0;
            padding: 28px clamp(16px, 3vw, 42px) 36px;
        }

        .admin-sidebar {
            padding: 24px 14px;
            border-right: 1px solid #d8e7e4;
            background: #fff;
        }

        .admin-sidebar-title {
            margin: 0 10px 16px;
            color: #647b79;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .admin-nav {
            display: grid;
            gap: 4px;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 40px;
            padding: 0 11px;
            border-radius: 5px;
            color: #173d3d;
            font-size: 14px;
            text-decoration: none;
        }

        .admin-nav-link i {
            width: 17px;
            color: #668380;
            text-align: center;
        }

        .admin-nav-link:hover,
        .admin-nav-link.active {
            color: #126b6b;
            background: #d9f2ef;
        }

        

        @media (max-width: 760px) {
            .show-section-layout {
                grid-template-columns: minmax(0, 1fr);
            }

            .admin-sidebar {
                padding: 10px 12px;
                border-right: 0;
                border-bottom: 1px solid #d8e7e4;
            }

            .admin-nav {
                display: flex;
                overflow-x: auto;
            }
        }
    </style>

    <div class="show-section-layout">
        @include('admin.partials.sidebar')

        <section class="show-section-main">
            <header class="mb-4">
                <p class="text-uppercase small fw-bold text-success mb-1">Admin console</p>
                <h1 class="h3 mb-0">Contact Inquiry #{{ $contact->id }}</h1>
                <p class="text-secondary small mt-2 mb-0">
                    Received {{ $contact->created_at->format('M d, Y H:i') }}
                    @if ($contact->replied_at)
                        &middot; Replied {{ $contact->replied_at->format('M d, Y H:i') }}
                    @else
                        &middot; <span class="text-warning fw-bold">Not replied yet</span>
                    @endif
                </p>
            </header>

            @if (session('status'))
                <div class="alert alert-success py-2" role="status">{{ session('status') }}</div>
            @endif

            <div class="card mb-4">
                <div class="card-header bg-primary text-white fw-bold">Submitted information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $contact->name }}</dd>

                        <dt class="col-sm-3">Username</dt>
                        <dd class="col-sm-9">
                            {{ $contact->username ?: '-' }}
                            @if ($recipient)
                                <span class="badge bg-success ms-1">Registered user</span>
                            @else
                                <span class="badge bg-secondary ms-1">Not registered</span>
                            @endif
                        </dd>

                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">
                            <a href="mailto:{{ $contact->email }}?subject=Re:%20Your%20inquiry%20%23{{ $contact->id }}">{{ $contact->email }}</a>
                        </dd>

                        <dt class="col-sm-3">Detail</dt>
                        <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $contact->detail }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white fw-bold">Reply to this user</div>
                <div class="card-body">
                    @if ($recipient)
                        <p class="small text-secondary">
                            The reply is sent as an <strong>Admin</strong> notification to
                            <strong>{{ $recipient->username ?: $recipient->name }}</strong>
                            and appears in their Notifications.
                        </p>
                        <form method="POST" action="{{ route('admin.contacts.reply', $contact) }}">
                            @csrf
                            <div class="mb-3">
                                <label for="reply-message" class="form-label">Message</label>
                                <textarea id="reply-message" name="message" rows="4"
                                    class="form-control @error('message') is-invalid @enderror"
                                    maxlength="5000" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-paper-plane me-1"></i>Send reply
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning py-2 mb-0" role="alert">
                            This inquirer is not a registered user, so no internal message can be sent.
                            Please reply by email to
                            <a href="mailto:{{ $contact->email }}?subject=Re:%20Your%20inquiry%20%23{{ $contact->id }}">{{ $contact->email }}</a>.
                        </div>
                    @endif
                </div>
            </div>

            <a href="{{ route('admin.contacts') }}" class="btn btn-outline-secondary btn-sm mt-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to contacts
            </a>
        </section>
    </div>
@endsection
