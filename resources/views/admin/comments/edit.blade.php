@extends('layouts.app')

@section('title', 'Edit Comment')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <h1 class="h4 mb-1">Edit comment #{{ $comment->id }}</h1>
    <p class="text-secondary small mb-4">
        By {{ $comment->user->name ?? 'Unknown user' }}
        @if ($comment->event)
            on <a href="{{ route('admin.events.show', $comment->event) }}">{{ $comment->event->title }}</a>
        @endif
    </p>

    <form action="{{ route('admin.comments.update', $comment) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="content" class="form-label">Comment</label>
            <textarea name="content" id="content" rows="5" class="form-control" required maxlength="1000">{{ old('content', $comment->content) }}</textarea>
            @error('content')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="language" class="form-label">Language</label>
            <input type="text" name="language" id="language" class="form-control" maxlength="5" required
                value="{{ old('language', $comment->language) }}">
            @error('language')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.comments') }}" class="btn btn-outline-warning btn-custom-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>
@endsection

