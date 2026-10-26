@extends('layouts.app-with-event-sidebar')

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
</style>

@php
    // DBの日時を datetime-local の入力欄が読める形(2026-10-17T09:00)に変換
    $toInput = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('Y-m-d\TH:i') : '';
@endphp

<div class="container py-4" style="max-width: 860px;">

    {{-- list --}}
    <nav class="mb-4 small">
        <a href="{{ route('events.index') }}" class="text-decoration-none fw-semibold text-dark">Events</a>
        <span class="mx-2">&gt;</span>
        <span class="text-muted">Edit an event</span>
    </nav>

    {{-- title --}}
    <p class="text-primary fw-semibold small mb-1">EDIT EVENT</p>
    <h1 class="fw-normal mb-1">Edit your event.</h1>
    <p class="event-form-help mb-4">Start to edit your event.</p>

    <form action="{{ route('events.update', $event) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
                       value="{{ old('title', $event->title) }}" maxlength="60" placeholder="Title">
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
                            <option value="{{ $category->id }}" @selected(old('category_id', $selectedCategoryId) == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Participation fee → price --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="price" class="event-form-label">Participation fee</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $event->price) }}" min="0" step="1" placeholder="e.g. 500">
                    <small class="event-form-help">If it is free, please enter 0.</small>
                    @error('price') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Start date → start_at --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="start_at" class="event-form-label">Start date</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="datetime-local" name="start_at" id="start_at" class="form-control @error('start_at') is-invalid @enderror"
                           value="{{ old('start_at', $toInput($event->start_at)) }}">
                    @error('start_at') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- End date → end_at --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="end_at" class="event-form-label">End date</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="datetime-local" name="end_at" id="end_at" class="form-control @error('end_at') is-invalid @enderror"
                           value="{{ old('end_at', $toInput($event->end_at)) }}">
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
                           value="{{ old('join_till', $toInput($event->join_till)) }}">
                    @error('join_till') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>

                {{-- Capacity --}}
                <div class="col-md-6">
                    <div class="d-flex justify-content-between">
                        <label for="capacity" class="event-form-label">Capacity</label>
                        <span class="event-form-required">REQUIRED</span>
                    </div>
                    <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror"
                           value="{{ old('capacity', $event->capacity) }}" min="1" placeholder="Number of people">
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
                          placeholder="What is this event? Who should join?">{{ old('description', $event->description) }}</textarea>
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
                   value="{{ old('location', $event->location) }}" maxlength="60" placeholder="e.g. Apas, Cebu City, Philippines">
            <small class="event-form-help">Maximum 60 characters.</small>
            @error('location') <div class="text-danger small">{{ $message }}</div> @enderror
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
            <small class="event-form-help">JPG or PNG, up to 2MB. Leave empty to keep the current image.</small>
            @error('image') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

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
                    <input type="radio" name="language" value="en" class="form-check-input me-2" @checked(old('language', $event->language) === 'en')>
                    English
                </label>
                <label class="event-option">
                    <input type="radio" name="language" value="ja" class="form-check-input me-2" @checked(old('language', $event->language) === 'ja')>
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
                    <input type="radio" name="visibility" value="public" class="form-check-input me-2" @checked(old('visibility', $event->visibility) === 'public')>
                    <strong>All users</strong>
                    <div class="event-form-help ms-4">Anyone can find, view and join this event.</div>
                </label>
                <label class="event-option">
                    <input type="radio" name="visibility" value="members" class="form-check-input me-2" @checked(old('visibility', $event->visibility) === 'members')>
                    <strong>Logged-in users</strong>
                    <div class="event-form-help ms-4">Only logged-in users can find, view and join this event.</div>
                </label>
                <label class="event-option">
                    <input type="radio" name="visibility" value="followers" class="form-check-input me-2" @checked(old('visibility', $event->visibility) === 'followers')>
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
                    <input type="radio" name="approval_required" value="1" class="form-check-input me-2" @checked(old('approval_required', (string) (int) $event->approval_required) === '1')>
                    <strong>Required</strong>
                    <div class="event-form-help ms-4">People can join this event after you approve them.</div>
                </label>
                <label class="event-option">
                    <input type="radio" name="approval_required" value="0" class="form-check-input me-2" @checked(old('approval_required', (string) (int) $event->approval_required) === '0')>
                    <strong>Not required</strong>
                    <div class="event-form-help ms-4">People can join this event right away.</div>
                </label>
            </div>
            @error('approval_required') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        {{-- button --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" class="btn btn-primary px-4">Edit event</button>
        </div>
    </form>
</div>
@endsection