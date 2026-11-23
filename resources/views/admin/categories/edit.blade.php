@extends('layouts.app')

@section('title', __('Edit Category'))

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <h1 class="h3 mb-4 text-center">{{ __('Edit Category') }}</h1>

            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label for="name" class="form-label">{{ __('Category Name') }}</label>
                    <input type="text" name="name" id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $category->name) }}" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="localized_name" class="form-label">{{ __('Localized category name (:language)', ['language' => $localeLabel]) }}</label>
                    <input type="text" name="localized_name" id="localized_name"
                        class="form-control @error('localized_name') is-invalid @enderror"
                        value="{{ old('localized_name', $localizedName) }}">
                    @error('localized_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-warning btn-custom-outline">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i>{{ __('Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection