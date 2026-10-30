@extends('layouts.app')

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

        .event-page-layout {
            width: 100%;
            max-width: none;
            padding-left: 16px;
            padding-right: 0;
            background-color: #ffffff;
            box-sizing: border-box;
        }

        .event-page-row {
            display: grid;
            grid-template-columns: 16.6666667% minmax(0, 1fr);
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .event-page-sidebar {
            min-width: 0;
            padding-right: 16px;
            background-color: #fff;
            box-sizing: border-box;
        }

        .event-page-content {
            min-width: 0;
            width: 100%;
            background-color: #f7f7f5;
        }

        /* サイドバー内のルート要素が狭くならないようにする */
        .event-page-sidebar>* {
            max-width: 100%;
            box-sizing: border-box;
        }

        /* 画面が狭い場合は縦並び */
        @media (max-width: 991.98px) {
            .event-page-row {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="event-page-layout">
        <div class="event-page-row">
            <aside class="event-page-sidebar">
                @include('events.partials.sidebar')
            </aside>
            <div class="event-page-content">
                <div class="event-container py-4  px-3 mx-auto" style="max-width: 860px;">

                    {{-- list --}}
                    <nav class="mb-4 small">
                        <a href="{{ route('events.index') }}" class="text-decoration-none fw-semibold text-dark">Events</a>
                        <span class="mx-2">&gt;</span>
                        <span class="text-muted">Create an event</span>
                    </nav>

                    {{-- title --}}
                    <p class="text-primary fw-semibold small mb-1">New event</p>
                    <h1 class="fw-normal mb-1">Make your event.</h1>
                    <p class="event-form-help mb-4">Fill in the details below to publish your event.</p>

                    <form action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
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
                                <input type="text" name="title" id="title"
                                    class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                                    maxlength="60" placeholder="Title">
                                <small class="event-form-help">Maximum 60 characters.</small>
                                @error('title')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-4">
                                {{-- Category --}}
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <label for="category_id" class="event-form-label">Category</label>
                                        <span class="event-form-required">REQUIRED</span>
                                    </div>
                                    <select name="category_id" id="category_id"
                                        class="form-select @error('category_id') is-invalid @enderror">
                                        <option value="" hidden>Select category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Participation fee → price --}}
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <label for="price" class="event-form-label">Participation fee</label>
                                        <span class="event-form-required">REQUIRED</span>
                                    </div>
                                    <input type="number" name="price" id="price"
                                        class="form-control @error('price') is-invalid @enderror"
                                        value="{{ old('price', 0) }}" min="0" step="1" placeholder="e.g. 500">
                                    <small class="event-form-help">If it is free, please enter 0.</small>
                                    @error('price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Start date → start_at --}}
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <label for="start_at" class="event-form-label">Start date</label>
                                        <span class="event-form-required">REQUIRED</span>
                                    </div>
                                    <input type="datetime-local" name="start_at" id="start_at"
                                        class="form-control @error('start_at') is-invalid @enderror"
                                        value="{{ old('start_at') }}">
                                    @error('start_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- End date → end_at --}}
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <label for="end_at" class="event-form-label">End date</label>
                                        <span class="event-form-required">REQUIRED</span>
                                    </div>
                                    <input type="datetime-local" name="end_at" id="end_at"
                                        class="form-control @error('end_at') is-invalid @enderror"
                                        value="{{ old('end_at') }}">
                                    <small class="event-form-help">If it is one day, enter the same date as the start
                                        date.</small>
                                    @error('end_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Register closed → join_till --}}
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <label for="join_till" class="event-form-label">Registration closes</label>
                                        <span class="event-form-required">REQUIRED</span>
                                    </div>
                                    <input type="datetime-local" name="join_till" id="join_till"
                                        class="form-control @error('join_till') is-invalid @enderror"
                                        value="{{ old('join_till') }}">
                                    @error('join_till')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Capacity --}}
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between">
                                        <label for="capacity" class="event-form-label">Capacity</label>
                                        <span class="event-form-required">REQUIRED</span>
                                    </div>
                                    <input type="number" name="capacity" id="capacity"
                                        class="form-control @error('capacity') is-invalid @enderror"
                                        value="{{ old('capacity') }}" min="1" placeholder="Number of people">
                                    @error('capacity')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
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
                                <small class="event-form-help">Help people understand what to expect. Maximum 500
                                    characters.</small>
                                @error('description')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
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
                            <input type="text" name="location" id="location"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location') }}" maxlength="60"
                                placeholder="e.g. Apas, Cebu City, Philippines">
                            <small class="event-form-help">Maximum 60 characters.</small>
                            @error('location')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 03 Event identity --}}
                        <div class="event-form-section">
                            <div class="d-flex gap-3 mb-4">
                                <div class="event-form-step">03</div>
                                <div>
                                    <h5 class="mb-0">Event identity</h5>
                                    <p class="event-form-help mb-0">Please add a visual that helps this event stand out.
                                    </p>
                                </div>
                            </div>

                            <label for="image" class="event-form-label">Cover image</label>
                            <input type="file" name="image" id="image" accept="image/*"
                                class="form-control @error('image') is-invalid @enderror">
                            <small class="event-form-help">JPG or PNG, up to 2MB.</small>
                            @error('image')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
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
                                    <input type="radio" name="language" value="en" class="form-check-input me-2"
                                        @checked(old('language', 'en') === 'en')>
                                    English
                                </label>
                                <label class="event-option">
                                    <input type="radio" name="language" value="ja" class="form-check-input me-2"
                                        @checked(old('language') === 'ja')>
                                    Japanese
                                </label>
                            </div>
                            @error('language')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
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
                                    <input type="radio" name="visibility" value="public" class="form-check-input me-2"
                                        @checked(old('visibility', 'public') === 'public')>
                                    <strong>All users</strong>
                                    <div class="event-form-help ms-4">Anyone can find, view and join this event.</div>
                                </label>
                                <label class="event-option">
                                    <input type="radio" name="visibility" value="members"
                                        class="form-check-input me-2" @checked(old('visibility') === 'members')>
                                    <strong>Logged-in users</strong>
                                    <div class="event-form-help ms-4">Only logged-in users can find, view and join this
                                        event.</div>
                                </label>
                                <label class="event-option">
                                    <input type="radio" name="visibility" value="followers"
                                        class="form-check-input me-2" @checked(old('visibility') === 'followers')>
                                    <strong>Followers only</strong>
                                    <div class="event-form-help ms-4">Only your followers can find and join this event.
                                    </div>
                                </label>
                            </div>
                            @error('visibility')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 06 Approval --}}
                        <div class="event-form-section">
                            <div class="d-flex gap-3 mb-4">
                                <div class="event-form-step">06</div>
                                <div>
                                    <h5 class="mb-0">Approval</h5>
                                    <p class="event-form-help mb-0">Please select whether you approve each person who
                                        joins.</p>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <label class="event-option">
                                    <input type="radio" name="approval_required" value="1"
                                        class="form-check-input me-2" @checked(old('approval_required', '1') === '1')>
                                    <strong>Required</strong>
                                    <div class="event-form-help ms-4">People can join this event after you approve them.
                                    </div>
                                </label>
                                <label class="event-option">
                                    <input type="radio" name="approval_required" value="0"
                                        class="form-check-input me-2" @checked(old('approval_required') === '0')>
                                    <strong>Not required</strong>
                                    <div class="event-form-help ms-4">People can join this event right away.</div>
                                </label>
                            </div>
                            @error('approval_required')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- button --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('events.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Create event</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
