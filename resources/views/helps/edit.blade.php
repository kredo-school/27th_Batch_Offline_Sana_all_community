@extends('layouts.app')

@section('sidebar', 'helps')

@section('title', __('Edit help'))

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
                {{ __('Helps') }}
            </a>

            <span class="mx-2">&gt;</span>

            <span class="text-muted">
                {{ __('Edit a help') }}
            </span>
        </nav>

        {{-- Title --}}
        <p class="text-primary fw-semibold small mb-1">
            {{ __('Edit help') }}
        </p>

        <h1 class="fw-normal mb-1">
            {{ __('Edit your help.') }}
        </h1>

        <p class="help-form-help mb-4">
            {{ __('Update the details below and save your changes.') }}
        </p>

        <form action="{{ route('helps.update', $help) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- 01 help basics --}}
            <div class="help-form-section">
                <div class="d-flex gap-3 mb-4">
                    <div class="help-form-step">
                        01
                    </div>

                    <div>
                        <h5 class="mb-0">
                            {{ __('Help basics') }}
                        </h5>

                        <p class="help-form-help mb-0">
                            {{ __('Please give some basic information about the help.') }}
                        </p>
                    </div>
                </div>

                {{-- Title --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="title" class="help-form-label">
                            {{ __('Help title') }}
                        </label>

                        <span class="help-form-required">
                            {{ __('REQUIRED') }}
                        </span>
                    </div>

                    <input type="text" name="title" id="title"
                        class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $help->title) }}"
                        maxlength="60" placeholder="{{ __('e.g. Laravel Backend Developer') }}" required>

                    <small class="help-form-help">
                        {{ __('Maximum 60 characters.') }}
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
                            {{ __('Requester type') }}
                        </label>

                        <span class="help-form-required">
                            {{ __('REQUIRED')}}
                        </span>
                    </div>

                    <div class="row g-2 mt-1">
                        @php
                            $requesterType = old('requester_type', $help->requester_type);

                            $requesterTypes = [
                                'individual' => __('Individual'),
                                'shop' => __('Shop'),
                                'company' => __('Company'),
                            ];
                        @endphp

                        @foreach ($requesterTypes as $value => $label)
                            <div class="col-md-4">
                                <label class="help-option">
                                    <input type="radio" name="requester_type" value="{{ $value }}"
                                        class="form-check-input me-2" @checked($requesterType === $value) required>

                                    {{ __($label) }}
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
                            {{ __('Company or display name') }}
                        </label>

                        <span class="help-form-help">
                            ({{ __('optional for individuals') }})
                        </span>
                    </div>

                    <input type="text" name="company_name" id="company_name"
                        class="form-control @error('company_name') is-invalid @enderror"
                        value="{{ old('company_name', $help->company_name) }}" maxlength="255"
                        placeholder="{{ __('e.g. Sana Corporation') }}">

                    <small class="help-form-help">
                        {{ __('Individuals can leave this field blank.') }}
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
                            {{ __('Category') }}
                        </label>

                        <span class="help-form-required">
                            {{ __('REQUIRED') }}
                        </span>
                    </div>

                    <select name="category_id" id="category_id"
                        class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="" hidden>
                            {{ __('Select category') }}
                        </option>

                        @php
                            $categoryId = old('category_id', $help->category_id);
                        @endphp

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($categoryId == $category->id)>
                                {{ __($category->name) }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                        <div class="text-danger small">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                @php
                    $eventStartAt = old('event_start_at', optional($help->event_start_at)->format('Y-m-d\TH:i'));

                    $eventEndAt = old('event_end_at', optional($help->event_end_at)->format('Y-m-d\TH:i'));

                    $applicationDeadline = old(
                        'application_deadline',
                        optional($help->application_deadline)->format('Y-m-d'),
                    );
                @endphp

                <div class="row g-4">
                    {{-- Event start --}}
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <label for="event_start_at" class="help-form-label">
                                {{ __('Start date and time') }}
                            </label>

                            <span class="help-form-required">
                                {{ __('REQUIRED') }}
                            </span>
                        </div>

                        <input type="datetime-local" name="event_start_at" id="event_start_at"
                            class="form-control @error('event_start_at') is-invalid @enderror" value="{{ $eventStartAt }}"
                            required>

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
                                {{ __('End date and time') }}
                            </label>

                            <span class="help-form-required">
                                {{ __('REQUIRED') }}
                            </span>
                        </div>

                        <input type="datetime-local" name="event_end_at" id="event_end_at"
                            class="form-control @error('event_end_at') is-invalid @enderror" value="{{ $eventEndAt }}"
                            required>

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
                                {{ __('Reward type') }}
                            </label>

                            <span class="help-form-required">
                                {{ __('REQUIRED') }}
                            </span>
                        </div>

                        @php
                            $rewardType = old('reward_type', $help->reward_type);
                        @endphp

                        <select name="reward_type" id="reward_type"
                            class="form-select @error('reward_type') is-invalid @enderror" required>
                            <option value="" hidden>
                                {{ __('Select reward type') }}
                            </option>

                            <option value="hourly" @selected($rewardType === 'hourly')>
                                {{ __('Hourly') }}
                            </option>

                            <option value="fixed" @selected($rewardType === 'fixed')>
                                {{ __('Fixed reward') }}
                            </option>

                            <option value="negotiable" @selected($rewardType === 'negotiable')>
                                {{ __('Negotiable') }}
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
                            {{ __('Reward amount') }}
                            <span class="help-form-help">
                                ({{ __('optional for negotiable') }})
                            </span>
                        </label>

                        <input type="number" name="reward_amount" id="reward_amount"
                            class="form-control @error('reward_amount') is-invalid @enderror"
                            value="{{ old('reward_amount', $help->reward_amount) }}" min="0" step="0.01"
                            placeholder="{{ __('e.g. 3000') }}">
                        
                        <small class="help-form-help">
                            {{ __('Set the unit to Philippine pesos') }}
                        </small>

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
                                {{ __('Number of helpers') }}
                            </label>

                            <span class="help-form-required">
                                {{ __('REQUIRED') }}
                            </span>
                        </div>

                        <input type="number" name="capacity" id="capacity"
                            class="form-control @error('capacity') is-invalid @enderror"
                            value="{{ old('capacity', $help->capacity ?? 1) }}" min="1" required>

                        @error('capacity')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Application deadline --}}
                    <div class="col-md-6">
                        <label for="application_deadline" class="help-form-label">
                            {{ __('Application deadline') }}
                            <span class="help-form-help">
                                ({{ __('optional') }})
                            </span>
                        </label>

                        <input type="date" name="application_deadline" id="application_deadline"
                            class="form-control @error('application_deadline') is-invalid @enderror"
                            value="{{ $applicationDeadline }}">

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
                            {{ __('Location') }}
                        </h5>

                        <p class="help-form-help mb-0">
                            {{ __('Please provide where the help will take place.') }}
                        </p>
                    </div>
                </div>

                <label for="location" class="help-form-label">
                    {{ __('Location') }}
                    <span class="help-form-help">
                        ({{ __('optional for online help') }})
                    </span>
                </label>

                <input type="text" name="location" id="location"
                    class="form-control @error('location') is-invalid @enderror"
                    value="{{ old('location', $help->location) }}" maxlength="100"
                    placeholder="{{ __('e.g. Cebu City, Philippines') }}">

                <small class="help-form-help">
                    {{ __('You can enter a city, country, or office address.') }}
                </small>

                @error('location')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror
                {{-- 地図で場所を選ぶ --}}
                <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                    <button type="button" id="location-search-btn" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-magnifying-glass"></i> {{ __('Find on map') }}
                    </button>
                    <small class="help-form-help">{{ __('Or click on the map to choose a place.') }}</small>
                </div>
                <div id="location-map" style="height: 320px; border-radius: 8px;"></div>
            </div>

            {{-- 03 help description $ requirements --}}
            <div class="help-form-section">
                <div class="d-flex gap-3 mb-4">
                    <div class="help-form-step">
                        03
                    </div>

                    <div>
                        <h5 class="mb-0">
                            {{ __('Help description & requirement') }}
                        </h5>

                        <p class="help-form-help mb-0">
                            {{ __('Please provide a detailed description and list the requirements for the type of support you need.') }}
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <label for="description" class="help-form-label">
                        {{ __('Description') }}
                    </label>

                    <span class="help-form-required">
                        {{ __('REQUIRED') }}
                    </span>
                </div>

                <textarea name="description" id="description" rows="8" maxlength="5000"
                    class="form-control @error('description') is-invalid @enderror"
                    placeholder="{{ __('Describe the task, date, location, and what the helper needs to do.') }}" required>{{ old('description', $help->description) }}</textarea>

                <small class="help-form-help">
                    {{ __('Maximum 5,000 characters.') }}
                </small>

                @error('description')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

                <div class="mt-4">
                    <label for="requirements" class="help-form-label">
                        {{ __('Requirements') }}
                        <span class="help-form-help">
                            ({{ __('optional') }})
                        </span>
                    </label>

                    <textarea name="requirements" id="requirements" rows="4" maxlength="2000"
                        class="form-control @error('requirements') is-invalid @enderror"
                        placeholder="{{ __('e.g. No experience required. Please wear comfortable clothes.') }}">{{ old('requirements', $help->requirements) }}</textarea>

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
                            {{ __('Help image') }}
                        </h5>

                        <p class="help-form-help mb-0">
                            {{ __('Add an image that represents this help or company.') }}
                        </p>
                    </div>
                </div>

                <label for="image" class="help-form-label">
                    {{ __('Cover image') }}
                    <span class="help-form-help">
                        ({{ __('optional') }})
                    </span>
                </label>

                <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp"
                    class="form-control @error('image') is-invalid @enderror">

                <small class="help-form-help">
                    {{ __('JPG, PNG, or WebP. Maximum 2MB.') }}
                </small>

                @error('image')
                    <div class="text-danger small">
                        {{ $message }}
                    </div>
                @enderror

                @include('events.partials.image-crop-editor', [
                    'imageUrl' => $help->image_path ? asset('storage/' . ltrim($help->image_path, '/')) : null,
                    'crop' => $help->image_crop ?? [],
                ])
            </div>

            {{-- Public notice --}}
            <div class="alert alert-info">
                {{ __('This help will be visible to all users after creation.') }}
            </div>

            {{-- Buttons --}}
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('helps.index') }}" class="btn btn-outline-secondary px-4">
                    {{ __('Cancel') }}
                </a>

                <button type="submit" class="btn btn-primary px-4">
                    {{ __('Update Help') }}
                </button>
            </div>
        </form>
    </div>
    {{-- 地図（Leaflet + OpenStreetMap） --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('location');
            const mapEl = document.getElementById('location-map');
            const searchBtn = document.getElementById('location-search-btn');
            if (!input || !mapEl || !searchBtn) return;

            // 最初はセブ市を表示
            const map = L.map(mapEl).setView([10.3157, 123.8854], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let marker = null;

            function placeMarker(lat, lng) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng]).addTo(map);
                }
            }

            // 長い住所を「場所, 市, 国」の形に短くする（最大100文字）
            function shortAddress(data) {
                const a = data.address || {};
                const parts = [
                    a.amenity || a.building || a.road || a.suburb || a.neighbourhood || a.village,
                    a.city || a.town || a.municipality,
                    a.country
                ].filter(Boolean);
                const text = parts.length ? parts.join(', ') : (data.display_name || '');
                return text.slice(0, 100);
            }

            // 地図をクリック → ピンを立てて住所を入力欄に入れる
            map.on('click', async function(e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=json&accept-language=en&lat=${e.latlng.lat}&lon=${e.latlng.lng}`
                        );
                    const data = await res.json();
                    input.value = shortAddress(data);
                } catch (err) {
                    console.error(err);
                }
            });

            // 「Find on map」→ 入力した住所を地図で探す
            searchBtn.addEventListener('click', async function() {
                const q = input.value.trim();
                if (!q) return;
                try {
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`
                        );
                    const results = await res.json();
                    if (results.length) {
                        const lat = parseFloat(results[0].lat);
                        const lng = parseFloat(results[0].lon);
                        map.setView([lat, lng], 16);
                        placeMarker(lat, lng);
                    } else {
                        alert(@json(__('Place not found. Try a different address.')));
                    }
                } catch (err) {
                    console.error(err);
                }
            });

            // バリデーションエラーで戻ってきたときは、入力済みの住所を地図に表示
            if (input.value.trim()) {
                searchBtn.click();
            }
        });
    </script>
@endsection
