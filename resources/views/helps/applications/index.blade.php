@extends('layouts.app')

@section('sidebar', 'helps')
@section('title', __('Help applications'))

@section('content')
    <style>
        .application-card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 16px;
        }

        .application-info-box {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .application-label {
            color: #888780;
            font-size: 11px;
        }

        .application-value {
            font-size: 14px;
            font-weight: 500;
        }

        .applicant-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #e6f1fb;
            color: #3d8fd6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
    </style>

    <div class="container py-4" style="max-width: 1000px;">
        <a href="{{ route('helps.show', $help) }}" class="text-decoration-none text-dark d-inline-block mb-3">
            &larr; {{ __('Back to help') }}
        </a>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('application'))
            <div class="alert alert-danger">
                {{ $errors->first('application') }}
            </div>
        @endif

        <div class="mb-4">
            <p class="text-primary fw-semibold small mb-1">
                {{ __('Help applications') }}
            </p>

            <h1 class="fw-normal mb-1">
                {{ __($help->title) }}
            </h1>

            <p class="text-muted mb-0">
                {{ __($help->company_name) }}
            </p>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0">
                {{ __('Applicants') }}
            </h2>

            <span class="text-muted small">
                {{ $applications->total() }} {{ __('applications')}}
            </span>
        </div>

        @forelse ($applications as $application)
            <article class="application-card">
                <div class="d-flex justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="applicant-avatar">
                            {{ strtoupper(substr($application->user->name ?? '?', 0, 1)) }}
                        </div>

                        <div>
                            <h3 class="h6 mb-1">
                                {{ __($application->user->name ?? 'Unknown user') }}
                            </h3>

                            <div class="text-muted small">
                                {{ $application->user->email ?? '' }}
                            </div>

                            <div class="text-muted small">
                                {{ __('Applied')}}
                                {{ $application->applied_at?->locale(app()->getLocale())->isoFormat('lll') }}
                            </div>
                        </div>
                    </div>

                    @if ($application->status === 'pending')
                        <span class="badge text-bg-warning align-self-start">
                            {{ __('Pending') }}
                        </span>
                    @elseif ($application->status === 'accepted')
                        <span class="badge text-bg-success align-self-start">
                            {{ __('Accepted') }}
                        </span>
                    @else
                        <span class="badge text-bg-secondary align-self-start">
                            {{ __('Rejected') }}
                        </span>
                    @endif
                </div>

                <div class="application-info-box mb-3">
                    <div class="application-label mb-1">
                        {{ __('Applicant message') }}
                    </div>

                    @if ($application->cover_letter)
                        <div class="application-value" style="white-space: pre-line;">
                            {{ $application->cover_letter }}
                        </div>
                    @else
                        <div class="text-muted small">
                            {{ __('No message provided.') }}
                        </div>
                    @endif
                </div>

                @if ($application->status === 'pending')
                    <div class="d-flex justify-content-end gap-2">
                        <form
                            action="{{ route('helps.applications.update-status', [
                                'help' => $help,
                                'application' => $application,
                            ]) }}"
                            method="POST">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="rejected">

                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                {{ __('Reject') }}
                            </button>
                        </form>

                        <form
                            action="{{ route('helps.applications.update-status', [
                                'help' => $help,
                                'application' => $application,
                            ]) }}"
                            method="POST">
                            @csrf
                            @method('PATCH')

                            <input type="hidden" name="status" value="accepted">

                            <button type="submit" class="btn btn-success btn-sm">
                                {{ __('Accept') }}
                            </button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <div class="application-card text-center py-5">
                <h3 class="h5 text-muted">
                    {{ __('No applications yet') }}
                </h3>

                <p class="text-muted small mb-0">
                    {{ __('Applicants will appear here when someone applies.') }}
                </p>
            </div>
        @endforelse

        @if ($applications instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
@endsection
