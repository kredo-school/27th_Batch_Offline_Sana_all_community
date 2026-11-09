<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileImages
{
    private array $created = [];

    private array $obsolete = [];

    public function apply(Profile $profile, array $data): void
    {
        $disk = Storage::disk('public');

        foreach (['avatar' => [512, 512, 'avatars'], 'cover_image' => [1600, 340, 'covers']] as $field => [$width, $height, $directory]) {
            $originalField = $field.'_original';
            $cropField = $field.'_crop';
            $oldFiles = array_filter([$profile->$field, $profile->$originalField]);

            if (! empty($data['remove_'.$field])) {
                $profile->$field = null;
                $profile->$originalField = null;
                $profile->$cropField = null;
                $this->obsolete = array_merge($this->obsolete, $oldFiles);

                continue;
            }

            $upload = $data[$field] ?? null;
            $previousCrop = $profile->$cropField ?? ['x' => 50, 'y' => 50, 'zoom' => 1];
            $crop = array_map('floatval', $data[$cropField] ?? ($upload ? ['x' => 50, 'y' => 50, 'zoom' => 1] : $previousCrop));
            if (! $upload && (! isset($data[$cropField]) || $crop == $previousCrop)) {
                continue;
            }

            $source = $upload ? null : ($profile->$originalField ?: $profile->$field);
            if (! $upload && ! $source) {
                continue;
            }
            // Existing external URLs are never fetched by the server.
            if (! $upload && (! str_starts_with($source, $directory.'/') || ! $disk->exists($source))) {
                throw ValidationException::withMessages([$field => 'Upload this image again to adjust its position.']);
            }

            $bytes = $upload ? $upload->get() : $disk->get($source);
            $size = @getimagesizefromstring($bytes);
            if (! $size || $size[0] * $size[1] > 24000000) {
                throw ValidationException::withMessages([$field => 'Choose an image with no more than 24 million pixels.']);
            }
            $image = @imagecreatefromstring($bytes);
            if (! $image) {
                throw ValidationException::withMessages([$field => 'This image could not be opened. Choose another image.']);
            }

            // Normalize phone-photo orientation for both future editing and the saved crop.
            if ($size['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
                $exif = @exif_read_data($upload ? $upload->getRealPath() : $disk->path($source));
                $orientation = (int) ($exif['Orientation'] ?? 1);
                if (in_array($orientation, [2, 4, 5, 7], true)) {
                    imageflip($image, IMG_FLIP_HORIZONTAL);
                }
                $angle = match ($orientation) {
                    3, 4 => 180,
                    6, 7 => -90,
                    5, 8 => 90,
                    default => 0,
                };
                if ($angle) {
                    $rotated = imagerotate($image, $angle, 0);
                    imagedestroy($image);
                    $image = $rotated;
                }
            }

            try {
                if ($upload) {
                    $source = $directory.'/originals/'.Str::uuid().'.png';
                    $this->writePng($source, $image);
                }
                $sourceWidth = imagesx($image);
                $sourceHeight = imagesy($image);
                $cropWidth = min($sourceWidth, $sourceHeight * $width / $height) / $crop['zoom'];
                $cropHeight = $cropWidth * $height / $width;
                $left = ($sourceWidth - $cropWidth) * $crop['x'] / 100;
                $top = ($sourceHeight - $cropHeight) * $crop['y'] / 100;
                $cropWidth = max(1, (int) round($cropWidth));
                $cropHeight = max(1, (int) round($cropHeight));
                $left = max(0, min($sourceWidth - $cropWidth, (int) round($left)));
                $top = max(0, min($sourceHeight - $cropHeight, (int) round($top)));
                $output = imagecreatetruecolor($width, $height);
                imagealphablending($output, false);
                imagesavealpha($output, true);
                imagefill($output, 0, 0, imagecolorallocatealpha($output, 0, 0, 0, 127));
                imagecopyresampled($output, $image, 0, 0, $left, $top, $width, $height, $cropWidth, $cropHeight);
                $path = $directory.'/'.Str::uuid().'.png';
                try {
                    $this->writePng($path, $output);
                } finally {
                    imagedestroy($output);
                }
                $profile->$field = $path;
                $profile->$originalField = $source;
                $profile->$cropField = $crop;
                $this->obsolete = array_merge($this->obsolete, array_diff($oldFiles, [$source]));
            } finally {
                imagedestroy($image);
            }
        }
    }

    private function writePng(string $path, \GdImage $image): void
    {
        imagesavealpha($image, true);
        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();
        $this->created[] = $path;
        if (! Storage::disk('public')->put($path, $bytes)) {
            throw new \RuntimeException('The profile image could not be saved.');
        }
    }

    public function discardNewFiles(): void
    {
        Storage::disk('public')->delete($this->created);
    }

    public function deleteObsoleteFiles(): void
    {
        $paths = array_filter(array_unique($this->obsolete), fn ($path) => str_starts_with($path, 'avatars/') || str_starts_with($path, 'covers/'));
        Storage::disk('public')->delete($paths);
    }
}
