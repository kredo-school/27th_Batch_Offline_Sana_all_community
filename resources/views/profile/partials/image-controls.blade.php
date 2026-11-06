@php
    $savedImage = $user->profile?->{$field};
    $originalImage = $user->profile?->{$field.'_original'} ?: $savedImage;
    $removed = (bool) old('remove_'.$field, false);
    $savedCrop = $user->profile?->{$field.'_crop'} ?? ['x' => 50, 'y' => 50, 'zoom' => 1];
    $uploadLost = ! $removed && (bool) old($field.'_upload_pending', false);
    $crop = $uploadLost ? $savedCrop : old($field.'_crop', $savedCrop);
    $crop = is_array($crop) ? $crop : $savedCrop;
    $crop = [
        'x' => min(100, max(0, (float) ($crop['x'] ?? $savedCrop['x']))),
        'y' => min(100, max(0, (float) ($crop['y'] ?? $savedCrop['y']))),
        'zoom' => min(3, max(1, (float) ($crop['zoom'] ?? $savedCrop['zoom']))),
    ];
    $canAdjust = $originalImage && ! str_starts_with($originalImage, 'https://') && ! str_starts_with($originalImage, 'http://');
@endphp
<div class="profile-image-controls" data-image-controls="{{ $field }}"
    data-source="{{ $originalImage ? $imageUrl($originalImage, $fallback) : '' }}"
    data-fallback="{{ $fallback }}" data-can-adjust="{{ $canAdjust ? '1' : '0' }}"
    data-saved-preview="{{ $imageUrl($savedImage, $fallback) }}" data-upload-lost="{{ $uploadLost ? '1' : '0' }}"
    data-render-crop="{{ ! $removed && $crop != $savedCrop ? '1' : '0' }}">
    <input type="hidden" name="remove_{{ $field }}" value="{{ $removed ? '1' : '0' }}">
    <input type="hidden" name="{{ $field }}_upload_pending" value="0">
    @foreach (['x', 'y', 'zoom'] as $property)
        <input type="hidden" name="{{ $field }}_crop[{{ $property }}]" value="{{ $crop[$property] ?? ($property === 'zoom' ? 1 : 50) }}">
    @endforeach
    <div class="profile-image-buttons">
        <button type="button" data-image-adjust @disabled(! $canAdjust || $removed)>Adjust {{ $label }}</button>
        <button type="button" data-image-remove @disabled(! $savedImage || $removed)>Remove {{ $label }}</button>
        <button type="button" data-image-undo @if(! $removed) hidden @endif>Undo removal</button>
    </div>
    <p class="profile-image-status" role="status" aria-live="polite">{{ $removed ? 'Image removed. Save Changes to confirm.' : ($savedImage && ! $canAdjust ? 'Upload this image again to adjust its position.' : '') }}</p>
</div>
