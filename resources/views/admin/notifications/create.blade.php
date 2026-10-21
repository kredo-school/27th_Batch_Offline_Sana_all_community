@extends('layouts.app')

@section('title', 'Create Notification')

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <h1 class="h3 mb-4">Create Notification</h1>
            <form action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf
                @include('admin.notifications.form', ['submitLabel' => 'Create'])
            </form>
        </div>
    </div>
</section>
@endsection