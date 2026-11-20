@extends('layouts.app')

@section('title', __('Edit Comment'))

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <h1 class="h4 mb-1">{{ __('Edit comment') }} #{{ $comment->id }}</h1>
    <p class="text-secondary small mb-4">
        {{ __('By') }} {{ $comment->user->name ?? __('Unknown user') }}
        @if ($comment->event)
            {{ __('on') }} <a href="{{ route('admin.events.show', $comment->event) }}">{{ $comment->event->title }}</a>
        @endif
    </p>

    <form action="{{ route('admin.comments.update', $comment) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="content" class="form-label">{{ __('Comment') }}</label>
            <textarea name="content" id="content" rows="5" class="form-control" required maxlength="1000">{{ old('content', $comment->content) }}</textarea>
            @error('content')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="language" class="form-label">{{ __('Language') }}</label>
            <select name="language" id="language" class="form-select" required>
                @foreach (config('app.supported_locales') as $value => $label)
                    <option value="{{ $value }}" @selected(old('language', $comment->language) === $value)>
                        {{ \App\Support\LocalizedValue::language($value) }}
                    </option>
                @endforeach
            </select>
            @error('language')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.comments') }}" class="btn btn-outline-warning btn-custom-outline">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
        </div>
    </form>
</div>
@endsection

