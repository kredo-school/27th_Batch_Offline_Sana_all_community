{{--
    イベントのカバー画像の位置調整（ドラッグ＋スライダー）
    使い方：@include('events.partials.image-crop-editor', ['imageUrl' => ..., 'crop' => ...])
--}}
@php
    $crop = $crop ?? [];
    $hasCrop = ! empty($crop);
    $zoom = old('image_crop.zoom', $crop['zoom'] ?? 1);
    $x = old('image_crop.x', $crop['x'] ?? 50);
    $y = old('image_crop.y', $crop['y'] ?? 50);
@endphp

<div id="crop-editor" class="mt-3 {{ $imageUrl ? '' : 'd-none' }}" data-has-crop="{{ $hasCrop ? '1' : '0' }}">
    <div class="event-form-label mb-1">Adjust cover position</div>
    <small class="event-form-help d-block mb-2">Drag the image to reposition it, or use the sliders below.</small>

    {{-- プレビュー（詳細ページと同じ横長の形） --}}
    <div id="crop-frame"
        style="position: relative; width: 100%; aspect-ratio: 16 / 9; overflow: hidden; border-radius: 12px; background: #eee; cursor: grab; touch-action: none;">
        <img id="crop-image" src="{{ $imageUrl }}" alt="" draggable="false"
            style="width: 100%; height: 100%; object-fit: contain; user-select: none; pointer-events: none;">
    </div>

    {{-- スライダー --}}
    <div class="row g-2 align-items-center mt-3">
        <label for="crop-zoom" class="col-4 col-md-3 mb-0">Zoom</label>
        <div class="col">
            <input type="range" id="crop-zoom" class="form-range" min="1" max="10" step="0.01" value="{{ $zoom }}">
        </div>
    </div>
    <div class="row g-2 align-items-center">
        <label for="crop-x" class="col-4 col-md-3 mb-0">Horizontal position</label>
        <div class="col">
            <input type="range" id="crop-x" class="form-range" min="0" max="100" step="0.1" value="{{ $x }}">
        </div>
    </div>
    <div class="row g-2 align-items-center">
        <label for="crop-y" class="col-4 col-md-3 mb-0">Vertical position</label>
        <div class="col">
            <input type="range" id="crop-y" class="form-range" min="0" max="100" step="0.1" value="{{ $y }}">
        </div>
    </div>

    <button type="button" id="crop-reset" class="btn btn-outline-secondary btn-sm mt-2">Reset position</button>

    {{-- 実際に送信される値 --}}
    <input type="hidden" name="image_crop[zoom]" id="crop-zoom-input" value="{{ $zoom }}">
    <input type="hidden" name="image_crop[x]" id="crop-x-input" value="{{ $x }}">
    <input type="hidden" name="image_crop[y]" id="crop-y-input" value="{{ $y }}">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editor = document.getElementById('crop-editor');
        if (!editor) return;

        const fileInput = document.getElementById('image');
        const frame = document.getElementById('crop-frame');
        const img = document.getElementById('crop-image');
        const zoomRange = document.getElementById('crop-zoom');
        const xRange = document.getElementById('crop-x');
        const yRange = document.getElementById('crop-y');
        const zoomInput = document.getElementById('crop-zoom-input');
        const xInput = document.getElementById('crop-x-input');
        const yInput = document.getElementById('crop-y-input');

        // 「ちょうど枠が埋まる」倍率（全体表示 → 枠いっぱい）
        let fillZoom = 1;
        // 新しい画像を選んだときだけ、読み込み後にリセットする
        let resetOnLoad = editor.dataset.hasCrop !== '1';

        function apply() {
            const zoom = parseFloat(zoomRange.value);
            const x = parseFloat(xRange.value);
            const y = parseFloat(yRange.value);
            img.style.objectPosition = `${x}% ${y}%`;
            img.style.transform = `scale(${zoom})`;
            img.style.transformOrigin = `${x}% ${y}%`;
            zoomInput.value = zoom;
            xInput.value = x;
            yInput.value = y;
        }

        function reset() {
            zoomRange.value = fillZoom;
            xRange.value = 50;
            yRange.value = 50;
            apply();
        }

        // 画像を読み込んだら、縦横比から「枠が埋まる倍率」を計算する
        img.addEventListener('load', function () {
            const frameRatio = 16 / 9;
            const imageRatio = img.naturalWidth / img.naturalHeight;
            fillZoom = imageRatio > frameRatio ? imageRatio / frameRatio : frameRatio / imageRatio;
            if (resetOnLoad) {
                reset();
                resetOnLoad = false;
            }
        });

        [zoomRange, xRange, yRange].forEach(function (range) {
            range.addEventListener('input', apply);
        });
        document.getElementById('crop-reset').addEventListener('click', reset);

        // 新しい画像を選んだら、プレビューを出して「枠いっぱい」から始める
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = fileInput.files[0];
                if (!file) return;
                resetOnLoad = true;
                img.src = URL.createObjectURL(file);
                editor.classList.remove('d-none');
            });
        }

        // 画像を直接つかんで動かす（マウス・タッチ両対応）
        let dragging = false;
        let startX = 0, startY = 0, startPosX = 50, startPosY = 50;

        frame.addEventListener('pointerdown', function (e) {
            dragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startPosX = parseFloat(xRange.value);
            startPosY = parseFloat(yRange.value);
            frame.setPointerCapture(e.pointerId);
            frame.style.cursor = 'grabbing';
        });

        frame.addEventListener('pointermove', function (e) {
            if (!dragging) return;
            const rect = frame.getBoundingClientRect();
            const zoom = parseFloat(zoomRange.value);
            // 右に動かすと画像も右へ動く → 見せる位置は左（値を小さく）
            const dx = (e.clientX - startX) / rect.width * 100 / zoom;
            const dy = (e.clientY - startY) / rect.height * 100 / zoom;
            xRange.value = Math.min(100, Math.max(0, startPosX - dx));
            yRange.value = Math.min(100, Math.max(0, startPosY - dy));
            apply();
        });

        ['pointerup', 'pointercancel'].forEach(function (type) {
            frame.addEventListener(type, function () {
                dragging = false;
                frame.style.cursor = 'grab';
            });
        });

        apply();
    });
</script>