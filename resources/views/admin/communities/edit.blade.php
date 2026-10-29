@extends('layouts.app')

@section('title', 'Edit Community')

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <h1 class="h3 mb-4 text-center">Edit Community</h1>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                <form action="{{ route('admin.communities.update', $community) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" name="name" type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $community->name) }}" maxlength="150" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="4"
                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $community->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="language" class="form-label">Language</label>
                            <input id="language" name="language" type="text"
                                class="form-control @error('language') is-invalid @enderror"
                                value="{{ old('language', $community->language) }}" maxlength="2" required>
                            @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="visibility" class="form-label">Visibility</label>
                            <select id="visibility" name="visibility"
                                class="form-select @error('visibility') is-invalid @enderror" required>
                                @foreach (['public', 'private'] as $option)
                                    <option value="{{ $option }}" @selected(old('visibility', $community->visibility) === $option)>
                                        {{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                            @error('visibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.communities') }}" class="btn btn-outline-warning btn-custom-outline">Back to communities</a>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
