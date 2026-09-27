<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploads under a dated folder. Raster images are downscaled to max 2000px and
 * re-encoded as WebP with GD when available, which keeps phone photos from the cafe light.
 */
class MediaUploader
{
    private const MAX_EDGE = 2000;

    public function store(UploadedFile $file, string $folder = 'uploads'): string
    {
        $disk = Storage::disk(config('loyal.uploads.disk'));
        $dir = trim($folder, '/').'/'.now()->format('Y/m');
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
        $name = Str::limit($base, 40, '').'-'.Str::lower(Str::random(6));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) && $this->canOptimise()) {
            $webp = $this->toWebp($file->getRealPath(), $ext);
            if ($webp !== null) {
                $path = "$dir/$name.webp";
                $disk->put($path, $webp, 'public');

                return $path;
            }
        }

        return $file->storeAs($dir, "$name.$ext", ['disk' => config('loyal.uploads.disk'), 'visibility' => 'public']);
    }

    public function delete(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http') && ! str_starts_with($path, '/')) {
            Storage::disk(config('loyal.uploads.disk'))->delete($path);
        }
    }

    private function canOptimise(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    private function toWebp(string $source, string $ext): ?string
    {
        $img = @imagecreatefromstring((string) file_get_contents($source));
        if (! $img) {
            return null;
        }

        if (in_array($ext, ['jpg', 'jpeg'], true) && function_exists('exif_read_data')) {
            $img = $this->applyExifOrientation($img, $source);
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::MAX_EDGE / max($w, $h));
        if ($scale < 1) {
            $nw = (int) round($w * $scale);
            $nh = (int) round($h * $scale);
            $resized = imagecreatetruecolor($nw, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }

        ob_start();
        imagewebp($img, null, 82);
        imagedestroy($img);

        return ob_get_clean() ?: null;
    }

    private function applyExifOrientation(\GdImage $img, string $source): \GdImage
    {
        $exif = @exif_read_data($source);
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180, 6 => -90, 8 => 90, default => 0,
        };

        return $angle ? (imagerotate($img, $angle, 0) ?: $img) : $img;
    }
}
