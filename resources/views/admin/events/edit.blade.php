@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <h1 class="h3 mb-4 text-center">Edit Event</h1>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                <form action="{{ route('admin.events.update', $event) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input id="title" name="title" type="text"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $event->title) }}" maxlength="200" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="4"
                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $event->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_at" class="form-label">Starts</label>
                            <input id="start_at" name="start_at" type="datetime-local"
                                class="form-control @error('start_at') is-invalid @enderror"
                                value="{{ old('start_at', $event->start_at?->format('Y-m-d\TH:i')) }}" required>
                            @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="join_till" class="form-label">Join till</label>
                            <input id="join_till" name="join_till" type="datetime-local"
                                class="form-control @error('join_till') is-invalid @enderror"
                                value="{{ old('join_till', $event->join_till?->format('Y-m-d\TH:i')) }}" required>
                            @error('join_till')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_at" class="form-label">Ends</label>
                            <input id="end_at" name="end_at" type="datetime-local"
                                class="form-control @error('end_at') is-invalid @enderror"
                                value="{{ old('end_at', $event->end_at?->format('Y-m-d\TH:i')) }}" required>
                            @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input id="location" name="location" type="text"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location', $event->location) }}">
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="region" class="form-label">Region</label>
                            <input id="region" name="region" type="text"
                                class="form-control @error('region') is-invalid @enderror"
                                value="{{ old('region', $event->region) }}">
                            @error('region')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="language" class="form-label">Language</label>
                            <select id="language" name="language" class="form-select @error('language') is-invalid @enderror" required>
                                @foreach (['en' => 'English', 'ja' => 'Japanese'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('language', $event->language) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="capacity" class="form-label">Capacity</label>
                            <input id="capacity" name="capacity" type="number" min="1"
                                class="form-control @error('capacity') is-invalid @enderror"
                                value="{{ old('capacity', $event->capacity) }}">
                            @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input id="price" name="price" type="number" min="0" step="0.01"
                                class="form-control @error('price') is-invalid @enderror"
                                value="{{ old('price', $event->price) }}" required>
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="visibility" class="form-label">Visibility</label>
                            <select id="visibility" name="visibility" class="form-select @error('visibility') is-invalid @enderror" required>
                                @foreach (['public' => 'Public', 'private' => 'Private'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('visibility', $event->visibility) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('visibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach (['draft' => 'Draft', 'published' => 'Published', 'cancelled' => 'Cancelled'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $event->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label">Categories</legend>
                        @php($selectedCategories = old('categories', $event->categories->modelKeys()))
                        <div class="row">
                            @foreach ($categories as $category)
                                <div class="col-sm-6 mb-2">
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]"
                                            value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategories))>
                                        <span class="form-check-label">{{ $category->name }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('categories')<div class="text-danger small">{{ $message }}</div>@enderror
                        @error('categories.*')<div class="text-danger small">{{ $message }}</div>@enderror
                    </fieldset>

                    <input type="hidden" name="approval_required" value="0">
                    <div class="form-check mb-4">
                        <input class="form-check-input" id="approval_required" name="approval_required" type="checkbox"
                            value="1" @checked(old('approval_required', $event->approval_required))>
                        <label class="form-check-label" for="approval_required">Require approval to join</label>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.events') }}" class="btn btn-outline-warning btn-custom-outline">Back to events</a>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
