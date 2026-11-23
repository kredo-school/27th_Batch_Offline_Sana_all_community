@extends('layouts.app')

@section('title', __('Create Notification'))

@section('content')
<section class="category-form-panel mx-auto">
    <div class="card mx-auto">
        <div class="card-body">
            <h1 class="h3 mb-4">{{ __('Create Notification') }}</h1>
            <form action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf
                @include('admin.notifications.form', ['submitLabel' => __('Create')])
            </form>
        </div>
    </div>
</section>
@endsection