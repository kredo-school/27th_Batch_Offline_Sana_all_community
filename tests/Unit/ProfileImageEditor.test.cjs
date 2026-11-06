const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class Element {
    constructor() {
        this.listeners = new Map();
        this.children = {};
        this.value = '';
        this.src = '';
        this.disabled = false;
        this.hidden = false;
        this.classList = { toggle() {} };
    }

    addEventListener(type, handler) {
        const handlers = this.listeners.get(type) || [];
        handlers.push(handler);
        this.listeners.set(type, handlers);
    }

    emit(type, properties = {}) {
        return Promise.all((this.listeners.get(type) || []).map(handler => handler({ type, ...properties })));
    }

    querySelector(selector) { return this.children[selector]; }
    querySelectorAll(selector) { return [this.children[selector]].flat().filter(Boolean); }
}

function editor() {
    const elements = {};
    const pending = new Map();
    const revoked = [];
    const animations = new Map();
    const previews = { avatar: [new Element(), new Element()], cover_image: [new Element(), new Element()] };
    let serial = 0;
    const controls = ['avatar', 'cover_image'].map(field => {
        const control = new Element();
        control.dataset = {
            imageControls: field, source: `/${field}-original.png`, canAdjust: '1',
            fallback: `/${field}-default.png`, savedPreview: `/${field}-saved.png`,
            uploadLost: '0', renderCrop: '0',
        };
        for (const selector of ['[data-image-adjust]', '[data-image-remove]', '[data-image-undo]', '.profile-image-status']) {
            control.children[selector] = new Element();
        }
        elements[field] = new Element();
        elements[field].files = [];
        elements[`remove_${field}`] = { value: '0' };
        elements[`${field}_upload_pending`] = { value: '0' };
        for (const [key, value] of Object.entries({ x: 50, y: 50, zoom: 1 })) {
            elements[`${field}_crop[${key}]`] = { value: String(value) };
        }
        previews[field].forEach(image => { image.src = control.dataset.savedPreview; });
        return control;
    });
    const canvas = new Element();
    canvas.getContext = () => ({
        clearRect() {},
        drawImage(image, x, y, w, h) { canvas.drawing = `${image.src}:${x},${y},${w},${h}`; },
    });
    canvas.toDataURL = () => `data:image/png;${canvas.drawing}`;
    canvas.getBoundingClientRect = () => ({ width: 200, height: 200 });
    canvas.setPointerCapture = () => {};
    const dialog = new Element();
    dialog.open = false;
    dialog.showModal = () => { dialog.open = true; };
    dialog.close = () => {
        if (!dialog.open) return;
        dialog.open = false;
        queueMicrotask(() => dialog.emit('close'));
    };
    for (const selector of ['[data-crop-frame]', '.profile-crop-feedback', '[data-crop-reset]', '[data-crop-apply]']) {
        dialog.children[selector] = new Element();
    }
    dialog.children.canvas = canvas;
    dialog.children['[data-crop-cancel]'] = [new Element(), new Element()];
    const sliders = { x: new Element(), y: new Element(), zoom: new Element() };
    const feedback = new Element();
    const document = {
        getElementById(id) {
            if (id === 'profile-edit-form') return { elements };
            if (id === 'profile-image-dialog') return dialog;
            if (id === 'profile-image-title') return new Element();
            return sliders[id.replace('profile-crop-', '')];
        },
        querySelector: () => feedback,
        querySelectorAll(selector) {
            if (selector === '[data-image-controls]') return controls;
            return previews[selector === '[data-avatar-preview]' ? 'avatar' : 'cover_image'];
        },
    };
    const context = {
        document, window: new Element(),
        Image: class {
            naturalWidth = 400;
            naturalHeight = 200;
            set src(url) {
                this.url = url;
                const requests = pending.get(url) || [];
                requests.push(this);
                pending.set(url, requests);
            }
            get src() { return this.url; }
        },
        DataTransfer: class {
            constructor() { this.files = []; this.items = { add: file => this.files.push(file) }; }
        },
        URL: {
            createObjectURL: file => `blob:${file.name}-${++serial}`,
            revokeObjectURL: url => revoked.push(url),
        },
        requestAnimationFrame(callback) { const id = ++serial; animations.set(id, callback); return id; },
        cancelAnimationFrame(id) { animations.delete(id); },
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../public/js/profile-image-editor.js'), 'utf8'), context);
    return {
        dialog, canvas, elements, previews, sliders, revoked, pending,
        control(field = 'avatar') { return controls.find(control => control.dataset.imageControls === field); },
        button(selector) { return dialog.children[selector]; },
        load(url) { const requests = pending.get(url) || []; assert.ok(requests.length); requests.forEach(image => image.onload()); pending.delete(url); },
        animate() { const callbacks = [...animations.values()]; animations.clear(); callbacks.forEach(callback => callback()); },
    };
}

function file(name) { return { name, type: 'image/png', size: 100 }; }

async function upload(ui, selected, apply = true) {
    ui.elements.avatar.files = [selected];
    const loading = ui.elements.avatar.emit('change');
    const source = [...ui.pending.keys()].find(url => url.startsWith('blob:'));
    ui.load(source);
    await loading;
    if (apply) await ui.button('[data-crop-apply]').emit('click');
    return source;
}

test('undoing removal retains the new upload marker and selected file', async () => {
    const ui = editor();
    const selected = file('new-photo.png');
    await upload(ui, selected);
    const preview = ui.previews.avatar[0].src;
    await ui.control().querySelector('[data-image-remove]').emit('click');
    assert.equal(ui.elements.avatar.files.length, 0);
    assert.equal(ui.elements.avatar_upload_pending.value, '0');
    await ui.control().querySelector('[data-image-undo]').emit('click');
    assert.equal(ui.elements.avatar.files[0], selected);
    assert.equal(ui.previews.avatar[0].src, preview);
    assert.equal(ui.elements.avatar_upload_pending.value, '1');
});

test('an old image finishing loading cannot replace a newly applied upload', async () => {
    const ui = editor();
    const oldLoad = ui.control().querySelector('[data-image-adjust]').emit('click');
    const selected = file('replacement.png');
    await upload(ui, selected);
    const preview = ui.previews.avatar[0].src;
    ui.load('/avatar-original.png');
    await oldLoad;
    assert.equal(ui.dialog.open, false);
    assert.equal(ui.previews.avatar[0].src, preview);
    assert.equal(ui.elements.avatar.files[0], selected);
});

test('removing a photo while it loads prevents an obsolete adjustment dialog from opening', async () => {
    const ui = editor();
    const oldLoad = ui.control().querySelector('[data-image-adjust]').emit('click');
    await ui.control().querySelector('[data-image-remove]').emit('click');
    ui.load('/avatar-original.png');
    await oldLoad;
    assert.equal(ui.dialog.open, false);
    assert.equal(ui.elements.remove_avatar.value, '1');
    assert.equal(ui.previews.avatar[0].src, '/avatar-default.png');
});

test('cancelling a replacement restores the previous upload and crop', async () => {
    const ui = editor();
    const previous = file('previous.png');
    const previousSource = await upload(ui, previous);
    const preview = ui.previews.avatar[0].src;
    const rejectedSource = await upload(ui, file('discarded.png'), false);
    ui.sliders.zoom.value = '2';
    await ui.sliders.zoom.emit('input');
    ui.animate();
    await ui.dialog.children['[data-crop-cancel]'][0].emit('click');
    await Promise.resolve();
    assert.equal(ui.previews.avatar[0].src, preview);
    assert.equal(ui.elements.avatar.files[0], previous);
    assert.equal(ui.elements['avatar_crop[zoom]'].value, 1);
    assert.ok(ui.revoked.includes(rejectedSource));
    assert.ok(!ui.revoked.includes(previousSource));
});

test('dragging changes the live preview while reset and cancel leave the saved fields unchanged', async () => {
    const ui = editor();
    const loading = ui.control().querySelector('[data-image-adjust]').emit('click');
    ui.load('/avatar-original.png');
    await loading;
    ui.sliders.zoom.value = '2';
    await ui.sliders.zoom.emit('input');
    ui.animate();
    const beforeDrag = ui.previews.avatar[0].src;
    await ui.canvas.emit('pointerdown', { pointerId: 1, pointerType: 'mouse', button: 0, clientX: 100, clientY: 100 });
    await ui.canvas.emit('pointermove', { pointerId: 1, clientX: 150, clientY: 100 });
    ui.animate();
    assert.notEqual(ui.previews.avatar[0].src, beforeDrag);
    assert.equal(ui.elements['avatar_crop[x]'].value, '50');
    await ui.button('[data-crop-reset]').emit('click');
    assert.equal(ui.sliders.zoom.value, 1);
    assert.equal(ui.sliders.x.value, 50);
    await ui.dialog.children['[data-crop-cancel]'][0].emit('click');
    await Promise.resolve();
    assert.equal(ui.previews.avatar[0].src, '/avatar-saved.png');
});
