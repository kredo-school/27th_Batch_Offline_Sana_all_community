@extends('layouts.app')

@section('title', 'Add Category')

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <h1 class="h3 mb-4 text-center">Add Category</h1>

            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" name="name" id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="name_ja" class="form-label">Japanese Category Name</label>
                    <input type="text" name="name_ja" id="name_ja"
                        class="form-control @error('name_ja') is-invalid @enderror"
                        value="{{ old('name_ja') }}">
                    @error('name_ja')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-warning">Cancel</a>
                    <button type="submit" class="btn btn-success">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection