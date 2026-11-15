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
        $name = $user->id.'-'.Str::lower(Str::random(16));
        $resized = $this->jpeg($file);

        if ($resized !== null) {
            $name .= '.jpg';
            Storage::disk('avatars')->put($name, $resized);
        } else {
            $extension = strtolower((string) $file->guessExtension());
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $extension = 'jpg';
            }
            $name .= '.'.$extension;
            $file->storeAs('', $name, 'avatars');
        }

        if (filled($user->avatar_path) && $user->avatar_path !== $name) {
            Storage::disk('avatars')->delete($user->avatar_path);
        }

        return $name;
    }

    private function jpeg(UploadedFile $file): ?string
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

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(256 / max($width, 1), 256 / max($height, 1), 1);
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
}
