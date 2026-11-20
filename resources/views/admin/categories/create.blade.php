@extends('layouts.app')

@section('title', str_replace(['[[', ']]'], '', __('Add category in English')))

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            @php
                $categoryTitle = __('Add category in English');
                $categoryTitleParts = [];
                preg_match('/^(.*?)\[\[(.*?)\]\](.*)$/u', $categoryTitle, $categoryTitleParts);
            @endphp
            <h1 class="h3 mb-4 text-center">
                @if (count($categoryTitleParts) === 4)
                    {{ $categoryTitleParts[1] }}<strong class="text-danger fw-bold">{{ $categoryTitleParts[2] }}</strong>{{ $categoryTitleParts[3] }}
                @else
                    {{ $categoryTitle }}
                @endif
            </h1>

            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="form-label">{{ __('Category Name') }}</label>
                    <input type="text" name="name" id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- @foreach (array_diff_key(config('app.supported_locales'), ['en' => true]) as $code => $nativeLabel)
                    <div class="mb-4">
                        <label for="translations_{{ $code }}" class="form-label">{{ __('Localized category name (:language)', ['language' => \App\Support\LocalizedValue::language($code)]) }}</label>
                        <input type="text" name="translations[{{ $code }}]" id="translations_{{ $code }}"
                            class="form-control @error('translations.'.$code) is-invalid @enderror"
                            value="{{ old('translations.'.$code) }}">
                        @error('translations.'.$code)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach --}}

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-warning">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-success">{{ __('Add category') }}</button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection