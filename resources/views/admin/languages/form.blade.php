@extends('layouts.app')

@section('title', $word === null ? __('Add word') : __('Edit word'))

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <h1 class="h3 mb-4 text-center">{{ $word === null ? __('Add word') : __('Edit word') }}</h1>

            <form action="{{ $action }}" method="POST">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="mb-4">
                    <label for="locale" class="form-label">{{ __('Language') }}</label>
                    <select name="locale" id="locale" class="form-select @error('locale') is-invalid @enderror"
                        @disabled($word !== null)>
                        @foreach ($locales as $code => $label)
                            <option value="{{ $code }}" @selected(old('locale', $locale) === $code)>{{ $label }} ({{ $code }})</option>
                        @endforeach
                    </select>
                    @if ($word !== null)
                        <input type="hidden" name="locale" value="{{ $locale }}">
                    @endif
                    @error('locale')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($word === null && $preferredLocale)
                        <div class="form-text">
                            {{ __('Your preferred language is selected. The new English word will also be translated into every other supported language.') }}
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label for="key" class="form-label">{{ __('Word key') }}</label>
                    <input type="text" name="key" id="key"
                        class="form-control @error('key') is-invalid @enderror"
                        value="{{ old('key', $key) }}" required>
                    @if ($word === null)
                        <div class="form-text">{{ __('Use the English word or phrase as the key. New translations are generated from English.') }}</div>
                    @endif
                    @error('key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="value" class="form-label">{{ __('Translation') }}</label>
                    <input type="text" name="value" id="value"
                        class="form-control @error('value') is-invalid @enderror"
                        value="{{ old('value', $word) }}" required>
                    @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.languages.index', ['locale' => $locale]) }}"
                        class="btn btn-outline-warning">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
