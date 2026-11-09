(() => {
    const form = document.getElementById('profile-edit-form');
    const dialog = document.getElementById('profile-image-dialog');
    if (!form || !dialog) return;

    const canvas = dialog.querySelector('canvas');
    const frame = dialog.querySelector('[data-crop-frame]');
    const sliders = Object.fromEntries(['x', 'y', 'zoom'].map(key => [key, document.getElementById(`profile-crop-${key}`)]));
    const feedback = document.querySelector('.profile-upload-feedback');
    const states = [];
    let active = null;
    let drag = null;
    let paint = null;
    let loading = 0;

    function previews(state) {
        return document.querySelectorAll(state.field === 'avatar' ? '[data-avatar-preview]' : '[data-cover-preview]');
    }

    function showPreview(state, url) {
        previews(state).forEach(image => { image.src = url; });
    }

    function syncControls(state) {
        state.remove.value = state.removed ? '1' : '0';
        state.control.querySelector('[data-image-adjust]').disabled = state.removed || !state.canAdjust;
        state.control.querySelector('[data-image-remove]').disabled = state.removed || !state.source;
        state.control.querySelector('[data-image-undo]').hidden = !state.removed;
        state.control.querySelector('.profile-image-status').textContent = state.removed
            ? 'Image removed. Save Changes to confirm.'
            : (state.uploadLost ? 'Choose your new image again before saving.'
                : (state.source && !state.canAdjust ? 'Upload this image again to adjust its position.' : ''));
    }

    function loadImage(url) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => {
                if (image.naturalWidth * image.naturalHeight > 24000000) {
                    reject(new Error('Choose an image with no more than 24 million pixels.'));
                } else {
                    resolve(image);
                }
            };
            image.onerror = () => reject(new Error('This image could not be opened. Upload another image.'));
            image.src = url;
        });
    }

    function cropBounds(image, crop, width, height) {
        const w = minCropWidth(image, width, height) / crop.zoom;
        const h = w * height / width;
        return { x: (image.naturalWidth - w) * crop.x / 100, y: (image.naturalHeight - h) * crop.y / 100, w, h };
    }

    function minCropWidth(image, width, height) {
        return Math.min(image.naturalWidth, image.naturalHeight * width / height);
    }

    function draw(image, crop, target) {
        const bounds = cropBounds(image, crop, target.width, target.height);
        const ctx = target.getContext('2d');
        ctx.clearRect(0, 0, target.width, target.height);
        ctx.drawImage(image, bounds.x, bounds.y, bounds.w, bounds.h, 0, 0, target.width, target.height);
    }

    function render() {
        if (!active) return;
        draw(active.image, active.crop, canvas);
        showPreview(active.state, canvas.toDataURL('image/png'));
    }

    function scheduleRender() {
        if (paint !== null) cancelAnimationFrame(paint);
        paint = requestAnimationFrame(() => { paint = null; render(); });
    }

    function syncSliders() {
        Object.entries(sliders).forEach(([key, input]) => { input.value = active.crop[key]; });
    }

    function restoreInput(state) {
        const files = new DataTransfer();
        if (state.file && !state.removed) files.items.add(state.file);
        state.input.files = files.files;
        form.elements[`${state.field}_upload_pending`].value = state.file && !state.removed ? '1' : '0';
    }

    function openEditor(state, image, source, file = null) {
        if (dialog.open) {
            if (file) URL.revokeObjectURL(source);
            return;
        }
        active = {
            state, image, source, file,
            crop: file ? { x: 50, y: 50, zoom: 1 } : { ...state.crop },
            previousPreview: previews(state)[0].src,
        };
        canvas.width = state.field === 'avatar' ? 512 : 1600;
        canvas.height = state.field === 'avatar' ? 512 : 340;
        frame.classList.toggle('is-avatar', state.field === 'avatar');
        document.getElementById('profile-image-title').textContent = state.field === 'avatar' ? 'Adjust profile photo' : 'Adjust cover';
        dialog.querySelector('.profile-crop-feedback').textContent = 'Changes are applied to your profile when you save.';
        syncSliders();
        render();
        dialog.showModal();
    }

    document.querySelectorAll('[data-image-controls]').forEach(control => {
        const field = control.dataset.imageControls;
        const state = {
            control, field, input: form.elements[field], remove: form.elements[`remove_${field}`],
            crop: Object.fromEntries(['x', 'y', 'zoom'].map(key => [key, Number(form.elements[`${field}_crop[${key}]`].value)])),
            source: control.dataset.source, canAdjust: control.dataset.canAdjust === '1',
            removed: form.elements[`remove_${field}`].value === '1', file: null, image: null,
            previousPreview: control.dataset.savedPreview, uploadLost: control.dataset.uploadLost === '1',
            revision: 0,
        };
        states.push(state);

        state.input.addEventListener('change', async () => {
            const file = state.input.files[0];
            if (!file) return;
            const revision = ++state.revision;
            restoreInput(state);
            if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type) || file.size > 6144 * 1024) {
                feedback.textContent = 'Choose a JPG, PNG or GIF image up to 6 MB.';
                return;
            }
            const ticket = ++loading;
            const url = URL.createObjectURL(file);
            try {
                const image = await loadImage(url);
                if (ticket !== loading || revision !== state.revision) { URL.revokeObjectURL(url); return; }
                feedback.textContent = '';
                openEditor(state, image, url, file);
            } catch (error) {
                URL.revokeObjectURL(url);
                if (revision === state.revision) feedback.textContent = error.message;
            }
        });

        control.querySelector('[data-image-adjust]').addEventListener('click', async () => {
            const revision = ++state.revision;
            const source = state.source;
            try {
                const image = state.image || await loadImage(source);
                if (revision !== state.revision || source !== state.source || state.removed) return;
                state.image = image;
                feedback.textContent = '';
                openEditor(state, image, source);
            } catch (error) {
                if (revision === state.revision) feedback.textContent = error.message;
            }
        });

        control.querySelector('[data-image-remove]').addEventListener('click', () => {
            state.revision++;
            state.previousPreview = previews(state)[0].src;
            state.removed = true;
            state.uploadLost = false;
            restoreInput(state);
            showPreview(state, control.dataset.fallback);
            syncControls(state);
        });

        control.querySelector('[data-image-undo]').addEventListener('click', () => {
            state.revision++;
            state.removed = false;
            restoreInput(state);
            showPreview(state, state.previousPreview || state.source);
            syncControls(state);
        });

        syncControls(state);
        if (control.dataset.renderCrop === '1' && state.canAdjust && !state.removed) {
            const source = state.source;
            const revision = state.revision;
            loadImage(source).then(image => {
                if (revision !== state.revision || state.source !== source || state.removed || active?.state === state) return;
                state.image = image;
                const preview = document.createElement('canvas');
                preview.width = field === 'avatar' ? 512 : 1600;
                preview.height = field === 'avatar' ? 512 : 340;
                draw(image, state.crop, preview);
                showPreview(state, preview.toDataURL('image/png'));
            }).catch(error => { if (revision === state.revision) feedback.textContent = error.message; });
        }
    });

    Object.entries(sliders).forEach(([key, input]) => input.addEventListener('input', () => {
        if (!active) return;
        active.crop[key] = Number(input.value);
        scheduleRender();
    }));

    canvas.addEventListener('pointerdown', event => {
        if (!active || (event.pointerType === 'mouse' && event.button !== 0)) return;
        const rect = canvas.getBoundingClientRect();
        drag = { id: event.pointerId, x: event.clientX, y: event.clientY, crop: { ...active.crop }, rect };
        canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', event => {
        if (!active || !drag || event.pointerId !== drag.id) return;
        const bounds = cropBounds(active.image, drag.crop, canvas.width, canvas.height);
        const remainingX = active.image.naturalWidth - bounds.w;
        const remainingY = active.image.naturalHeight - bounds.h;
        if (remainingX > 0) active.crop.x = Math.max(0, Math.min(100, drag.crop.x - (event.clientX - drag.x) * bounds.w / drag.rect.width / remainingX * 100));
        if (remainingY > 0) active.crop.y = Math.max(0, Math.min(100, drag.crop.y - (event.clientY - drag.y) * bounds.h / drag.rect.height / remainingY * 100));
        syncSliders();
        scheduleRender();
    });
    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(name => canvas.addEventListener(name, () => { drag = null; }));

    dialog.querySelector('[data-crop-reset]').addEventListener('click', () => {
        if (!active) return;
        active.crop = { x: 50, y: 50, zoom: 1 };
        syncSliders();
        render();
    });
    dialog.querySelectorAll('[data-crop-cancel]').forEach(button => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('close', () => {
        if (active) {
            showPreview(active.state, active.previousPreview);
            if (active.file) URL.revokeObjectURL(active.source);
        }
        active = null;
        drag = null;
    });
    dialog.querySelector('[data-crop-apply]').addEventListener('click', () => {
        if (!active) return;
        render();
        const state = active.state;
        if (active.file) {
            if (state.source.startsWith('blob:')) URL.revokeObjectURL(state.source);
            state.file = active.file;
            state.source = active.source;
        }
        state.image = active.image;
        state.canAdjust = true;
        state.removed = false;
        state.uploadLost = false;
        state.crop = { ...active.crop };
        state.revision++;
        Object.entries(state.crop).forEach(([key, value]) => { form.elements[`${state.field}_crop[${key}]`].value = value; });
        restoreInput(state);
        syncControls(state);
        active = null;
        dialog.close();
    });

    window.addEventListener('pagehide', event => {
        if (event.persisted) return;
        states.forEach(state => { if (state.source.startsWith('blob:')) URL.revokeObjectURL(state.source); });
    });
})();
