<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarStore
{
    public function store(UploadedFile $file, User $user): string
    {
        return $this->storeOnDisk($file, 'avatars', (string) $user->id, $user->avatar_path, 256);
    }

    public function storeOnDisk(UploadedFile $file, string $disk, string $prefix, ?string $previous = null, int $maxEdge = 256): string
    {
        $name = $prefix.'-'.Str::lower(Str::random(16));
        $resized = $this->jpeg($file, $maxEdge);

        if ($resized !== null) {
            $name .= '.jpg';
            Storage::disk($disk)->put($name, $resized);
        } else {
            $extension = strtolower((string) $file->guessExtension());
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $extension = 'jpg';
            }
            $name .= '.'.$extension;
            $file->storeAs('', $name, $disk);
        }

        if (filled($previous) && $previous !== $name) {
            Storage::disk($disk)->delete($previous);
        }

        return $name;
    }

    private function jpeg(UploadedFile $file, int $maxEdge): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return null;
        }

        $contents = file_get_contents($file->getRealPath());
        if ($contents === false) {
            return null;
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            return null;
        }

        $source = $this->applyOrientation($source, $this->orientation($file));

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min($maxEdge / max($width, 1), $maxEdge / max($height, 1), 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($target, null, 85);
        $binary = ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        return is_string($binary) && $binary !== '' ? $binary : null;
    }

    private function orientation(UploadedFile $file): int
    {
        if (! function_exists('exif_read_data')) {
            return 1;
        }

        $exif = @exif_read_data($file->getRealPath());
        $value = (int) ($exif['Orientation'] ?? 1);

        return ($value >= 1 && $value <= 8) ? $value : 1;
    }

    private function applyOrientation(\GdImage $image, int $orientation): \GdImage
    {
        return match ($orientation) {
            2 => $this->flip($image, IMG_FLIP_HORIZONTAL),
            3 => $this->rotate($image, 180),
            4 => $this->flip($image, IMG_FLIP_VERTICAL),
            5 => $this->rotate($this->flip($image, IMG_FLIP_HORIZONTAL), 90),
            6 => $this->rotate($image, -90),
            7 => $this->rotate($this->flip($image, IMG_FLIP_HORIZONTAL), -90),
            8 => $this->rotate($image, 90),
            default => $image,
        };
    }

    private function flip(\GdImage $image, int $mode): \GdImage
    {
        imageflip($image, $mode);

        return $image;
    }

    private function rotate(\GdImage $image, float $angle): \GdImage
    {
        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
