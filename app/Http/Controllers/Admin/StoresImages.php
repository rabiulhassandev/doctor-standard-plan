<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait StoresImages
{
    protected function storeImage(UploadedFile $file): string
    {
        $source = imagecreatefromstring($file->getContent());
        if ($source === false) {
            throw ValidationException::withMessages(['image' => 'This image could not be processed.']);
        }
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, 1600 / max($width, $height));
        $image = imagecreatetruecolor((int) max(1, round($width * $scale)), (int) max(1, round($height * $scale)));
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image, $source, 0, 0, 0, 0, imagesx($image), imagesy($image), $width, $height);
        ob_start();
        imagewebp($image, null, 85);
        $contents = ob_get_clean();
        imagedestroy($source);
        imagedestroy($image);
        $path = 'media/'.Str::uuid().'.webp';
        if (! Storage::disk('local')->put($path, $contents)) {
            throw ValidationException::withMessages(['image' => 'The image could not be saved. Please try again.']);
        }

        return $path;
    }
}
