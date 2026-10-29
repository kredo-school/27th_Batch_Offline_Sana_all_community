@extends('layouts.app')

@section('title', 'Conversation')

@section('content')
    <section class="category-form-panel mx-auto">
        <div class="card mx-auto">
            <div class="card-body">
                <a href="{{ route('admin.messages') }}" class="small text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Back to messages
                </a>

                <h1 class="h3 mt-3 mb-4">
                    {{ $message->sender?->username ?? 'Deleted user' }}
                    <i class="fa-solid fa-arrows-left-right mx-2 text-secondary" aria-hidden="true"></i>
                    {{ $message->receiver?->username ?? 'Deleted user' }}
                    <span class="badge bg-secondary ms-2">{{ $conversation->count() }}</span>
                </h1>

                <div class="d-flex flex-column gap-3">
                    @foreach ($conversation as $item)
                        <div @class(['border rounded p-3', 'border-primary' => $item->id === $message->id])>
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <strong>{{ $item->sender?->username ?? 'Deleted user' }}</strong>
                                    <span class="text-secondary small ms-2">{{ $item->created_at?->format('M d, Y H:i') }}</span>
                                </div>
                                <form action="{{ route('admin.messages.destroy', $item) }}" method="POST"
                                    data-confirm="true" data-confirm-message="Delete this message?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" aria-label="Delete message">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                            <p class="mb-0 mt-2" style="white-space: pre-wrap;">{{ $item->message }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
