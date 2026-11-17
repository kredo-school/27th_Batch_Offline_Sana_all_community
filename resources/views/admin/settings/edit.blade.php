@extends('layouts.app')

@section('title', __('Site Settings'))

@section('content')
<style>
    .admin-dashboard {
        --admin-ink: #173d3d;
        --admin-muted: #647b79;
        --admin-line: #d8e7e4;
        display: grid;
        grid-template-columns: 210px minmax(0, 1fr);
        min-height: calc(100vh - 74px);
        color: var(--admin-ink);
        background: #f5f8f7;
    }

    .admin-sidebar {
        padding: 24px 14px;
        border-right: 1px solid var(--admin-line);
        background: #fff;
    }

    .admin-sidebar-title {
        margin: 0 10px 16px;
        color: var(--admin-muted);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .admin-nav { display: grid; gap: 4px; }
    .admin-nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        min-height: 40px;
        padding: 0 11px;
        border-radius: 6px;
        color: var(--admin-ink);
        text-decoration: none;
        font-size: 13px;
        white-space: nowrap;
    }
    .admin-nav-link:hover,
    .admin-nav-link.active { color: #126b6b; background: #e8f5f1; }
    .admin-main { min-width: 0; padding: 28px 22px 40px; }
    .admin-heading { display: flex; align-items: end; justify-content: space-between; gap: 16px; }
    .admin-eyebrow { margin: 0 0 5px; color: #16817e; font-size: 15px; font-weight: 700; text-transform: uppercase; }
    .admin-heading h1 { margin: 0; font-size: 25px; font-weight: 700; }
    .settings-panel { border: 1px solid var(--admin-line); border-radius: 6px; background: #fff; }
    .settings-panel h2 { color: var(--admin-ink); }
    .settings-panel + .settings-panel { margin-top: 16px; }
    .settings-panel textarea { min-height: 150px; resize: vertical; }
    .policy-editor { padding-bottom: 16px; border-bottom: 1px solid #e6efed; }
    .policy-editor:last-child { padding-bottom: 0; border-bottom: 0; }
    .policy-history { margin: 8px 0 10px; color: var(--admin-muted); font-size: 13px; }
    .policy-history summary { cursor: pointer; color: #126b6b; font-weight: 600; }
    .policy-history pre {
        max-height: 220px;
        margin: 8px 0 0;
        padding: 12px;
        overflow: auto;
        white-space: pre-wrap;
        border: 1px solid #e6efed;
        border-radius: 4px;
        color: var(--admin-ink);
        background: #f7faf9;
        font: inherit;
    }
    .settings-panel .form-control,
    .settings-panel .form-select { border-color: #cbdedb; }
    @media (max-width: 760px) {
        .admin-dashboard { grid-template-columns: minmax(0, 1fr); }
        .admin-sidebar { padding: 10px 12px; border-right: 0; border-bottom: 1px solid var(--admin-line); }
        .admin-sidebar-title { margin-bottom: 8px; }
        .admin-nav { display: grid; overflow: visible; }
        .admin-nav-link { width: 100%; }
        .admin-main { padding: 22px 12px 32px; }
    }
</style>
<div class="admin-dashboard">
    @include('admin.partials.sidebar')
    <main class="admin-main">
        <header class="admin-heading mb-4">
            <div>
                <p class="admin-eyebrow text-uppercase fw-bold text-success mb-1">{{ __('Admin console') }}</p>
                <h1 class="mb-0 fw-medium">{{ __('Site Settings') }}</h1>
                <p class="text-secondary mb-0">{{ __('Manage the homepage, header categories, and public policies.') }}</p>
            </div>
        </header>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ __('Please correct the highlighted fields.') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <section class="settings-panel p-4 mb-4">
                <h2 class="h5">{{ __('Homepage') }}</h2>
                <label class="form-label" for="home_background">{{ __('Background image') }}</label>
                <input class="form-control" id="home_background" name="home_background" type="file" accept=".jpeg,.jpg,.png,.gif,image/jpeg,image/png,image/gif">
                <div class="form-text">{{ __('JPEG, JPG, PNG, and GIF files are supported. Large images are optimized before upload.') }}</div>
                @error('home_background')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @if (!empty($settings['home_background']))
                    <p class="small text-secondary mt-2">{{ __('A custom background is currently active.') }}</p>
                @endif
            </section>

            <section class="settings-panel p-4 mb-4">
                <h2 class="h5">{{ __('Header categories') }}</h2>
                <p class="text-secondary small">{{ __('Choose the categories shown in the header, in order. Leave a position empty to hide it.') }}</p>
                @php($ordered = json_decode($settings['header_category_order'] ?? '[]', true) ?: ['Events', 'Meet up', 'Sports', 'Exchange', 'Jobs'])
                @for ($position = 0; $position < max(5, count($ordered)); $position++)
                    <select class="form-select mb-2" name="header_category_order[]" aria-label="{{ __('Header category position') }} {{ $position + 1 }}">
                        <option value="">{{ __('Hidden') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->name }}" @selected(($ordered[$position] ?? null) === $category->name)>{{ $category->localized_name }}</option>
                        @endforeach
                    </select>
                @endfor
            </section>

            <section class="settings-panel p-4 mb-4">
                <h2 class="h5">{{ __('Terms and policies') }}</h2>
                @foreach ([
                    'terms' => 'Terms and Privacy', 'guidelines' => 'Community Guidelines', 'report' => 'Report & Content Removal Policy',
                    'event' => 'Event organizer term', 'job' => 'Job posting policy', 'advertising' => 'Business Advertising Policy', 'p2p' => 'Peer-to-Peer Transaction Policy',
                ] as $suffix => $label)
                    @php($policyValue = old('policy_'.$suffix, $settings['policy_'.$suffix] ?? ''))
                    <div class="policy-editor mb-3">
                        <label class="form-label" for="policy_{{ $suffix }}">{{ __($label) }}</label>
                        <details class="policy-history">
                            <summary>{{ __('Show previous text') }}</summary>
                            <pre>{{ $policyValue !== '' ? $policyValue : __('No custom text is saved. The public page uses its built-in policy.') }}</pre>
                        </details>
                        <textarea class="form-control" id="policy_{{ $suffix }}" name="policy_{{ $suffix }}" rows="6">{{ $policyValue }}</textarea>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2 policy-cancel" data-target="policy_{{ $suffix }}">
                            <i class="fa-solid fa-rotate-left me-1"></i>{{ __('Cancel') }}
                        </button>
                    </div>
                @endforeach
            </section>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Save Changes') }}</button>
        </form>
        <script>
            document.querySelectorAll('.policy-cancel').forEach(function (button) {
                var textarea = document.getElementById(button.dataset.target);
                var initialValue = textarea.value;

                button.addEventListener('click', function () {
                    textarea.value = initialValue;
                    textarea.focus();
                });
            });

            document.getElementById('home_background').addEventListener('change', function (event) {
                var input = event.target;
                var file = input.files[0];

                if (!file || file.size <= 1900000 || !file.type.match(/^image\/(jpeg|png|gif)$/)) {
                    return;
                }

                var image = new Image();
                var reader = new FileReader();
                reader.onload = function () { image.src = reader.result; };
                image.onload = function () {
                    var scale = Math.min(1, 1920 / image.width, 1080 / image.height);
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(image.width * scale));
                    canvas.height = Math.max(1, Math.round(image.height * scale));
                    canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob(function (blob) {
                        if (!blob || blob.size >= file.size) {
                            return;
                        }

                        var optimized = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' });
                        var transfer = new DataTransfer();
                        transfer.items.add(optimized);
                        input.files = transfer.files;
                    }, 'image/jpeg', 0.82);
                };
                reader.readAsDataURL(file);
            });
        </script>
    </main>
</div>
@endsection
