@extends('layouts.app')

@section('title', __('Translate'))

@section('content')
<div class="container py-4 py-md-5">
    <div class="mx-auto" style="max-width: 1000px;">
        <div class="mb-4 text-center">
            <h1 class="fw-bold">{{ __('Translate') }}</h1>
            <p class="text-muted mb-0">{{ __('Translate text securely with LibreTranslate.') }}</p>
        </div>

        <form id="mirror-translation-form"
              action="{{ route('translate.store') }}"
              data-error="{{ __('Translation is unavailable right now.') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold" for="mirror-source">{{ __('From') }}</label>
                    <select id="mirror-source" name="source" class="form-select">
                        <option value="auto">{{ __('Detect language') }}</option>
                        @foreach ($supportedLocales as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-center">
                    <i class="fa-solid fa-arrow-right text-muted" aria-hidden="true"></i>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold" for="mirror-target">{{ __('To') }}</label>
                    <select id="mirror-target" name="target" class="form-select">
                        @foreach ($supportedLocales as $code => $label)
                            <option value="{{ $code }}" @selected($code === app()->getLocale())>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="mirror-source-text">{{ __('Original text') }}</label>
                    <textarea id="mirror-source-text" name="text" class="form-control" rows="9"
                              maxlength="5000" required placeholder="{{ __('Type or paste text here...') }}"></textarea>
                    <div class="form-text text-end"><span id="mirror-character-count">0</span>/5000</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="mirror-result">{{ __('Translation') }}</label>
                    <textarea id="mirror-result" class="form-control bg-light" rows="9" readonly
                              aria-live="polite" placeholder="{{ __('Your translation will appear here.') }}"></textarea>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                <p id="mirror-translation-status" class="text-muted mb-0" role="status"></p>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-language me-1" aria-hidden="true"></i>
                    {{ __('Translate') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
