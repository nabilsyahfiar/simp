<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ImageCompressor
{
    public static function compressPublicImage(
        string $path,
        int $maxDimension = 1920,
        int $jpegQuality = 82,
        int $pngCompression = 7
    ): void {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return;
        }

        $absolutePath = $disk->path($path);
        $imageInfo = @getimagesize($absolutePath);

        if (! is_array($imageInfo)) {
            return;
        }

        [$width, $height, $imageType] = $imageInfo;

        if ($width < 1 || $height < 1) {
            return;
        }

        if (! in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            return;
        }

        $source = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
            default => null,
        };

        if (! $source) {
            return;
        }

        if ($imageType === IMAGETYPE_JPEG) {
            $source = self::applyJpegOrientation($source, $absolutePath);
        }

        $newWidth = $width;
        $newHeight = $height;

        if (max($width, $height) > $maxDimension) {
            $ratio = $maxDimension / max($width, $height);
            $newWidth = max(1, (int) round($width * $ratio));
            $newHeight = max(1, (int) round($height * $ratio));
        }

        $target = $source;

        if ($newWidth !== $width || $newHeight !== $height) {
            $target = imagecreatetruecolor($newWidth, $newHeight);

            if (! $target) {
                imagedestroy($source);

                return;
            }

            if ($imageType === IMAGETYPE_PNG) {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
                imagefilledrectangle($target, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($source);
        }

        if ($imageType === IMAGETYPE_JPEG) {
            @imagejpeg($target, $absolutePath, max(1, min(100, $jpegQuality)));
        } else {
            @imagepng($target, $absolutePath, max(0, min(9, $pngCompression)));
        }

        imagedestroy($target);
    }

    private static function applyJpegOrientation($image, string $absolutePath)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($absolutePath);
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? null) : null;

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }
}

