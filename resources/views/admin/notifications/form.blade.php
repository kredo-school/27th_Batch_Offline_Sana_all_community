<div class="mb-3">
    <label for="user_id" class="form-label">{{ __('Send to') }}</label>
    @php
        $selectedUser = (string) old('user_id', $notification->user_id ?? '');
    @endphp
    @if ($notification?->recipient_label)
        <input type="text" class="form-control" value="{{ $notification->recipient_label }}" disabled>
        <input type="hidden" name="user_id" value="{{ $notification->user_id }}">
    @else
    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
        <option value="">{{ __('Select recipient') }}</option>
        @unless ($notification)
            <optgroup label="{{ __('Everyone with role') }}">
                <option value="admin" @selected($selectedUser === 'admin')>{{ __('All admins') }}</option>
                <option value="user" @selected($selectedUser === 'user')>{{ __('All users') }}</option>
            </optgroup>
        @endunless
        <optgroup label="{{ __('Single user') }}">
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected($selectedUser === (string) $user->id)>
                    {{ $user->name }}@if ($user->username) ({{ $user->username }})@endif
                </option>
            @endforeach
        </optgroup>
    </select>
    @endif
    @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label d-block">{{ __('From') }}</label>
    @php
        $currentFrom = old('from', ($notification->sender_name ?? __('Admin')) === __('Admin') ? 'admin' : 'self');
        $myName = Auth::user()->username ?: Auth::user()->name;
    @endphp
    <div class="btn-group" role="group" aria-label="{{ __('Sender') }}">
        <input type="radio" class="btn-check" name="from" id="from_admin" value="admin" autocomplete="off"
            @checked($currentFrom === 'admin')>
        <label class="btn btn-outline-primary" for="from_admin">{{ __('Admin') }}</label>
        <input type="radio" class="btn-check" name="from" id="from_self" value="self" autocomplete="off"
            @checked($currentFrom === 'self')>
        <label class="btn btn-outline-primary" for="from_self">{{ $myName }}</label>
    </div>
    @error('from')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label d-block">{{ __('Type') }}</label>
    @php
        $currentType = old('type', $notification->type ?? '');
        $typeOptions = ['general' => __('General'), 'maintenance' => __('Maintenance'), 'update' => __('Update')];
    @endphp
    <div class="btn-group" role="group" aria-label="{{ __('Notification type') }}">
        @foreach ($typeOptions as $value => $label)
            <input type="radio" class="btn-check" name="type" id="type_{{ $value }}" value="{{ $value }}"
                autocomplete="off" @checked($currentType === $value) required>
            <label class="btn btn-outline-primary" for="type_{{ $value }}">{{ $label }}</label>
        @endforeach
    </div>
    @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="title" class="form-label">{{ __('Title') }}</label>
    <input type="text" name="title" id="title" maxlength="255"
        class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $notification->title ?? '') }}">
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="message" class="form-label">{{ __('Message') }}</label>
    <textarea name="message" id="message" rows="4"
        class="form-control @error('message') is-invalid @enderror">{{ old('message', $notification->message ?? '') }}</textarea>
    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4">
    <label for="read_at" class="form-label">{{ __('Read at (optional)') }}</label>
    <input type="datetime-local" name="read_at" id="read_at"
        class="form-control @error('read_at') is-invalid @enderror"
        value="{{ old('read_at', isset($notification?->read_at) ? $notification->read_at->format('Y-m-d\TH:i') : '') }}">
    @error('read_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.notifications') }}" class="btn btn-outline-warning btn-custom-outline">{{ __('Cancel') }}</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>