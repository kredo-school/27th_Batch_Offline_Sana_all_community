@extends('layouts.app')

@section('sidebar', 'helps')
@section('title', 'Create help')

@section('content')
    <style>
        .help-form-section {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 20px;
        }

        .help-form-step {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e6f1fb;
            color: #3d8fd6;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .help-form-label {
            font-weight: 600;
            font-size: 14px;
        }

        .help-form-required {
            color: #3d8fd6;
            font-size: 11px;
        }

        .help-form-help {
            color: #888780;
            font-size: 12px;
        }

        .help-form-section .form-control:focus,
        .help-form-section .form-select:focus {
            border-color: #3d8fd6;
            box-shadow: 0 0 0 0.25rem rgba(61, 143, 214, 0.25);
        }

        .help-option {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 14px 16px;
            cursor: pointer;
            display: block;
        }

        .help-option:has(input:checked) {
            background: #e6f1fb;
            border-color: #3d8fd6;
        }
    </style>

    <div class="container py-4" style="max-width: 860px;">
        {{-- Breadcrumb --}}
        <nav class="mb-4 small">
            <a href="{{ route('helps.index') }}" class="text-decoration-none fw-semibold text-dark">
                Helps
            </a>

            <span class="mx-2">&gt;</span>

            <span class="text-muted">
                Create a help
            </span>
        </nav>

        {{-- Title --}}
        <p class="text-primary fw-semibold small mb-1">
            New help
        </p>

        <h1 class="fw-normal mb-1">
            Create your help.
        </h1>

        <p class="help-form-help mb-4">
            Fill in the details below to publish your help.
        </p>

        <form action="{{ route('helps.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 01 help basics --}}
            <div class="help-form-section">
                <div class="d-flex gap-3 mb-4">
                    <div class="help-form-step">
                        01
                    </div>

                    <div>
                        <h5 class="mb-0">
                            Help basics
                        </h5>

                        <p class="help-form-help mb-0">
                            Please give some basic information about the help.
                        </p>
                    </div>
                </div>

                {{-- Title --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="title" class="help-form-label">
                            Help title
                        </label>

                        <span class="help-form-required">
                            REQUIRED
                        </span>
                    </div>

                    <input type="text" name="title" id="title"
                        class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" maxlength="60"
                        placeholder="e.g. Laravel Backend Developer" required>

                    <small class="help-form-help">
                        Maximum 60 characters.
                    </small>

                    @error('title')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Requester type --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="help-form-label">
                            Requester type
                        </label>

                        <span class="help-form-required">
                            REQUIRED
                        </span>
                    </div>

                    <div class="row g-2 mt-1">
                        @php
                            $requesterTypes = [
                                'individual' => 'Individual',
                                'shop' => 'Shop',
                                'company' => 'Company',
                            ];
                        @endphp

                        @foreach ($requesterTypes as $value => $label)
                            <div class="col-md-4">
                                <label class="help-option">
                                    <input type="radio" name="requester_type" value="{{ $value }}"
                                        class="form-check-input me-2" @checked(old('requester_type') === $value) required>

                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @error('requester_type')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Company or display name --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="company_name" class="help-form-label">
                            Company or display name
                        </label>

                        <span class="help-form-help">
                            (optional for individuals)
                        </span>
                    </div>

                    <input type="text" name="company_name" id="company_name"
                        class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}"
                        maxlength="255" placeholder="e.g. Sana Corporation">

                    <small class="help-form-help">
                        Individuals can leave this field blank.
                    </small>

                    @error('company_name')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Category --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="category_id" class="help-form-label">
                            Category
                        </label>

                        <span class="help-form-required">
                            REQUIRED
                        </span>
                    </div>

                    <select name="category_id" id="category_id"
                        class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="" hidden>
                            Select category
                        </option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="row g-4">
                    {{-- Event start --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <label for="event_start_at" class="help-form-label">
                                Start date and time
                            </label>

                            <span class="help-form-required">
                                REQUIRED
                            </span>
                        </div>

                        <input type="datetime-local" name="event_start_at" id="event_start_at"
                            class="form-control @error('event_start_at') is-invalid @enderror"
                            value="{{ old('event_start_at') }}" required>

                        @error('event_start_at')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Event end --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <label for="event_end_at" class="help-form-label">
                                End date and time
                            </label>

                            <span class="help-form-required">
                                REQUIRED
                            </span>
                        </div>

                        <input type="datetime-local" name="event_end_at" id="event_end_at"
                            class="form-control @error('event_end_at') is-invalid @enderror"
                            value="{{ old('event_end_at') }}" required>

                        @error('event_end_at')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Reward type --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <label for="reward_type" class="help-form-label">
                                Reward type
                            </label>

                            <span class="help-form-required">
                                REQUIRED
                            </span>
                        </div>

                        <select name="reward_type" id="reward_type"
                            class="form-select @error('reward_type') is-invalid @enderror" required>
                            <option value="" hidden>
                                Select reward type
                            </option>

                            <option value="hourly" @selected(old('reward_type') === 'hourly')>
                                Hourly
                            </option>

                            <option value="fixed" @selected(old('reward_type') === 'fixed')>
                                Fixed reward
                            </option>

                            <option value="negotiable" @selected(old('reward_type') === 'negotiable')>
                                Negotiable
                            </option>
                        </select>

                        @error('reward_type')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Reward amount --}}
                    <div class="col-md-6">
                        <label for="reward_amount" class="help-form-label">
                            Reward amount
                            <span class="help-form-help">
                                (optional for negotiable)
                            </span>
                        </label>

                        <input type="number" name="reward_amount" id="reward_amount"
                            class="form-control @error('reward_amount') is-invalid @enderror"
                            value="{{ old('reward_amount') }}" min="0" step="0.01" placeholder="e.g. 3000">

                        @error('reward_amount')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Capacity --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <label for="capacity" class="help-form-label">
                                Number of helpers
                            </label>

                            <span class="help-form-required">
                                REQUIRED
                            </span>
                        </div>

                        <input type="number" name="capacity" id="capacity"
                            class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', 1) }}"
                            min="1" required>

                        @error('capacity')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Application deadline --}}
                    <div class="col-md-6">
                        <label for="application_deadline" class="help-form-label">
                            Application deadline
                            <span class="help-form-help">
                                (optional)
                            </span>
                        </label>

                        <input type="date" name="application_deadline" id="application_deadline"
                            class="form-control @error('application_deadline') is-invalid @enderror"
                            value="{{ old('application_deadline') }}">

                        @error('application_deadline')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 02 Location --}}
            <div class="help-form-section">
                <div class="d-flex gap-3 mb-4">
                    <div class="help-form-step">
                        02
                    </div>

                    <div>
                        <h5 class="mb-0">
                            Location
                        </h5>

                        <p class="help-form-help mb-0">
                            Please provide where the help will take place.
                        </p>
                    </div>
                </div>

                <label for="location" class="help-form-label">
                    Location
                    <span class="help-form-help">
                        (optional for online help)
                    </span>
                </label>

                <input type="text" name="location" id="location"
                    class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}"
                    maxlength="100" placeholder="e.g. Cebu City, Philippines">

                <small class="help-form-help">
                    You can enter a city, country, or office address.
                </small>

                @error('location')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- 03 help description $ requirements --}}
            <div class="help-form-section">
                <div class="d-flex gap-3 mb-4">
                    <div class="help-form-step">
                        03
                    </div>

                    <div>
                        <h5 class="mb-0">
                            Help description & requirement
                        </h5>

                        <p class="help-form-help mb-0">
                            Please provide a detailed description and list the requirements for the type of support you need.
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <label for="description" class="help-form-label">
                        Description
                    </label>

                    <span class="help-form-required">
                        REQUIRED
                    </span>
                </div>

                <textarea name="description" id="description" rows="8" maxlength="5000"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="Describe the task, date, location, and what the helper needs to do." required>{{ old('description') }}</textarea>

                <small class="help-form-help">
                    Maximum 5,000 characters.
                </small>

                @error('description')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

                <div class="mt-4">
                    <label for="requirements" class="help-form-label">
                        Requirements
                        <span class="help-form-help">
                            (optional)
                        </span>
                    </label>

                    <textarea name="requirements" id="requirements" rows="4" maxlength="2000"
                        class="form-control @error('requirements') is-invalid @enderror"
                        placeholder="e.g. No experience required. Please wear comfortable clothes.">{{ old('requirements') }}</textarea>

                    @error('requirements')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>


            {{-- 04 help image --}}
            <div class="help-form-section">
                <div class="d-flex gap-3 mb-4">
                    <div class="help-form-step">
                        04
                    </div>

                    <div>
                        <h5 class="mb-0">
                            Help image
                        </h5>

                        <p class="help-form-help mb-0">
                            Add an image that represents this help or company.
                        </p>
                    </div>
                </div>

                <label for="image" class="help-form-label">
                    Cover image
                    <span class="help-form-help">
                        (optional)
                    </span>
                </label>

                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                    class="form-control @error('image') is-invalid @enderror">

                <small class="help-form-help">
                    JPG, PNG, or WebP. Maximum 2MB.
                </small>

                @error('image')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Public notice --}}
            <div class="alert alert-info">
                This help will be visible to all users after creation.
            </div>

            {{-- Buttons --}}
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('helps.index') }}" class="btn btn-outline-secondary px-4">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary px-4">
                    Create Help
                </button>
            </div>
        </form>
    </div>
@endsection
