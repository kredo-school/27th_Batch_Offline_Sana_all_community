@extends('layouts.app')

@section('sidebar', $community ? 'community' : 'events')

@section('content')
<style>
    .event-form-section {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 20px;
    }
    .event-form-step {
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
    .event-form-label {
        font-weight: 600;
        font-size: 14px;
    }
    .event-form-required {
        color: #3d8fd6;
        font-size: 11px;
    }
    .event-form-help {
        color: #888780;
        font-size: 12px;
    }
    .event-option {
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 14px 16px;
        cursor: pointer;
        display: block;
    }
    .event-option:has(input:checked) {
        background: #e6f1fb;
        border-color: #3d8fd6;
    }
    @if ($community)
    /* コミュニティから開いたときはボタン以外をオレンジにする */
    .community-accent {
        color: #F58634;
    }
    .event-form-step {
        background: #fdebdc;
        color: #F58634;
    }
    .event-form-required {
        color: #F58634;
    }
    .event-option:has(input:checked) {
        background: #fdebdc;
        border-color: #F58634;
    }
    .event-form-section .form-check-input:checked {
        background-color: #F58634;
        border-color: #F58634;
    }
    .event-form-section .form-control:focus,
    .event-form-section .form-select:focus,
    .event-form-section .form-check-input:focus {
        border-color: #F58634;
        box-shadow: 0 0 0 0.25rem rgba(245, 134, 52, 0.25);
    }
    @endif
</style>

<div class="container py-4" style="max-width: 860px;">

    {{-- list --}}
    <nav class="mb-4 small">
        @if ($community)
            <a href="{{ route('community.index') }}" class="text-decoration-none fw-semibold text-dark">Communities</a>
            <span class="mx-2">&gt;</span>
            <a href="{{ route('community.show', $community->id) }}" class="text-decoration-none fw-semibold text-dark">{{ $community->name }}</a>
            <span class="mx-2">&gt;</span>
            <span class="community-accent fw-semibold" aria-current="page">Create an event</span>
        @else
            <a href="{{ route('events.index') }}" class="text-decoration-none fw-semibold text-dark">Events</a>
            <span class="mx-2">&gt;</span>
            <span class="text-muted">Create an event</span>
        @endif
    </nav>

    {{-- title --}}
    <p class="{{ $community ? 'community-accent' : 'text-primary' }} fw-semibold small mb-1">New event</p>
    <h1 class="fw-normal mb-1">Make your event.</h1>
    <p class="event-form-help mb-4">
        {{ $community ? "Fill in the details below to publish your event in {$community->name}." : 'Fill in the details below to publish your event.' }}
    </p>

    <form action="{{ $community ? route('community.events.store', $community->id) : route('events.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- 01 Event basics --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">01</div>
                <div>
                    <h5 class="mb-0">Event basics</h5>
                    <p class="event-form-help mb-0">Please give some basic information about your event.</p>
                </div>
            </div>

            {{-- Title --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label for="title" class="event-form-label">Title</label>
                    <span class="event-form-required">REQUIRED</span>
                </div>
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" maxlength="60" placeholder="Title">
                <small class="event-form-help">Maximum 60 characters.</small>
                @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="row g-4">
                {{-- Category --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="category_id" class="event-form-label">Category</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="" hidden>Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Participation fee → price --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="price" class="event-form-label">Participation fee (per person)</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', 0) }}" min="0" step="1" placeholder="e.g. 500">
                    <small class="event-form-help">If it is free, please enter 0.</small>
                    @error('price') <div class="text-danger small">{{ $message }}</div> @enderror
                                        {{-- Fee note --}}
                    <label for="fee_note" class="event-form-label mt-3">Fee note <span class="event-form-help">(optional)</span></label>
                    <input type="text" name="fee_note" id="fee_note" maxlength="100"
                           class="form-control @error('fee_note') is-invalid @enderror"
                           value="{{ old('fee_note') }}"
                           placeholder="e.g. Order individually, approx. 220 PHP~">
                    <small class="event-form-help">If the cost varies, enter 0 above and explain here.</small>
                    @error('fee_note') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Start date → start_at --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="start_at" class="event-form-label">Start date</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="datetime-local" name="start_at" id="start_at" class="form-control @error('start_at') is-invalid @enderror"
                           value="{{ old('start_at') }}">
                    @error('start_at') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- End date → end_at --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="end_at" class="event-form-label">End date</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="datetime-local" name="end_at" id="end_at" class="form-control @error('end_at') is-invalid @enderror"
                           value="{{ old('end_at') }}">
                    <small class="event-form-help">If it is one day, enter the same date as the start date.</small>
                    @error('end_at') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Register closed → join_till --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="join_till" class="event-form-label">Registration closes</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="datetime-local" name="join_till" id="join_till" class="form-control @error('join_till') is-invalid @enderror"
                           value="{{ old('join_till') }}">
                    @error('join_till') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Capacity --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="capacity" class="event-form-label">Capacity</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror"
                           value="{{ old('capacity') }}" min="1" placeholder="Number of people">
                    @error('capacity') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Description --}}
            <div class="mt-4">
                <div class="d-flex justify-content-between">
                    <label for="description" class="event-form-label">Description</label>
                    <span class="event-form-required">REQUIRED</span>
                </div>
                <textarea name="description" id="description" rows="4" maxlength="500"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="What is this event? Who should join?">{{ old('description') }}</textarea>
                <small class="event-form-help">Help people understand what to expect. Maximum 500 characters.</small>
                @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- 02 Location --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">02</div>
                <div>
                    <h5 class="mb-0">Location</h5>
                    <p class="event-form-help mb-0">Please give the event address.</p>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <label for="location" class="event-form-label">Address</label>
                <span class="event-form-required">REQUIRED</span>
            </div>
            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror"
                   value="{{ old('location') }}" maxlength="60" placeholder="e.g. Apas, Cebu City, Philippines">
            <small class="event-form-help">Maximum 60 characters.</small>
            @error('location') <div class="text-danger small">{{ $message }}</div> @enderror
                        {{-- 地図で場所を選ぶ --}}
            <div class="d-flex align-items-center gap-2 mt-3 mb-2">
                <button type="button" id="location-search-btn" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-magnifying-glass"></i> Find on map
                </button>
                <small class="event-form-help">Or click on the map to choose a place.</small>
            </div>
            <div id="location-map" style="height: 320px; border-radius: 8px;"></div>
        </div>

        {{-- 03 Event identity --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">03</div>
                <div>
                    <h5 class="mb-0">Event identity</h5>
                    <p class="event-form-help mb-0">Please add a visual that helps this event stand out.</p>
                </div>
            </div>

            <label for="image" class="event-form-label">Cover image</label>
            <input type="file" name="image" id="image" accept="image/*" class="form-control @error('image') is-invalid @enderror">
            <small class="event-form-help">JPG or PNG, up to 2MB.</small>
            @error('image') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        {{-- 言語・公開範囲・承認はイベントページの作成だけで選ぶ（コミュニティ内は既定値で作る） --}}
        @unless ($community)
        {{-- 04 Language --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">04</div>
                <div>
                    <h5 class="mb-0">Language</h5>
                    <p class="event-form-help mb-0">Please select the language used in this event.</p>
                </div>
            </div>

            <div class="d-grid gap-2">
                <label class="event-option">
                    <input type="radio" name="language" value="en" class="form-check-input me-2" @checked(old('language', 'en') === 'en')>
                    English
                </label>
                <label class="event-option">
                    <input type="radio" name="language" value="ja" class="form-check-input me-2" @checked(old('language') === 'ja')>
                    Japanese
                </label>
            </div>
            @error('language') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        {{-- 05 Visibility --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">05</div>
                <div>
                    <h5 class="mb-0">Visibility</h5>
                    <p class="event-form-help mb-0">Please select who can find and join this event.</p>
                </div>
            </div>

            <div class="d-grid gap-2">
                <label class="event-option">
                    <input type="radio" name="visibility" value="public" class="form-check-input me-2" @checked(old('visibility', 'public') === 'public')>
                    <strong>All users</strong>
                    <div class="event-form-help ms-4">Anyone can find, view and join this event.</div>
                </label>
                <label class="event-option">
                    <input type="radio" name="visibility" value="members" class="form-check-input me-2" @checked(old('visibility') === 'members')>
                    <strong>Logged-in users</strong>
                    <div class="event-form-help ms-4">Only logged-in users can find, view and join this event.</div>
                </label>
                <label class="event-option">
                    <input type="radio" name="visibility" value="followers" class="form-check-input me-2" @checked(old('visibility') === 'followers')>
                    <strong>Followers only</strong>
                    <div class="event-form-help ms-4">Only your followers can find and join this event.</div>
                </label>
            </div>
            @error('visibility') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        {{-- 06 Approval --}}
        <div class="event-form-section">
            <div class="d-flex gap-3 mb-4">
                <div class="event-form-step">06</div>
                <div>
                    <h5 class="mb-0">Approval</h5>
                    <p class="event-form-help mb-0">Please select whether you approve each person who joins.</p>
                </div>
            </div>

            <div class="d-grid gap-2">
                <label class="event-option">
                    <input type="radio" name="approval_required" value="1" class="form-check-input me-2" @checked(old('approval_required', '1') === '1')>
                    <strong>Required</strong>
                    <div class="event-form-help ms-4">People can join this event after you approve them.</div>
                </label>
                <label class="event-option">
                    <input type="radio" name="approval_required" value="0" class="form-check-input me-2" @checked(old('approval_required') === '0')>
                    <strong>Not required</strong>
                    <div class="event-form-help ms-4">People can join this event right away.</div>
                </label>
            </div>
            @error('approval_required') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>
        @endunless

        {{-- button --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ $community ? route('community.show', $community->id) : route('events.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">Create event</button>
        </div>
    </form>
</div>
{{-- 地図（Leaflet + OpenStreetMap） --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        // 長い住所を「場所, 市, 国」の形に短くする（最大60文字）
        function shortAddress(data) {
            const a = data.address || {};
            const parts = [
                a.amenity || a.building || a.road || a.suburb || a.neighbourhood || a.village,
                a.city || a.town || a.municipality,
                a.country
            ].filter(Boolean);
            const text = parts.length ? parts.join(', ') : (data.display_name || '');
            return text.slice(0, 60);
        }

        // 地図をクリック → ピンを立てて住所を入力欄に入れる
        map.on('click', async function (e) {
            placeMarker(e.latlng.lat, e.latlng.lng);
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&accept-language=en&lat=${e.latlng.lat}&lon=${e.latlng.lng}`);
                const data = await res.json();
                input.value = shortAddress(data);
            } catch (err) {
                console.error(err);
            }
        });

        // 「Find on map」→ 入力した住所を地図で探す
        searchBtn.addEventListener('click', async function () {
            const q = input.value.trim();
            if (!q) return;
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`);
                const results = await res.json();
                if (results.length) {
                    const lat = parseFloat(results[0].lat);
                    const lng = parseFloat(results[0].lon);
                    map.setView([lat, lng], 16);
                    placeMarker(lat, lng);
                } else {
                    alert('Place not found. Try a different address.');
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
