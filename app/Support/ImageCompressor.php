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

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth < 1 || $sourceHeight < 1) {
            imagedestroy($source);

            return;
        }

        $newWidth = $sourceWidth;
        $newHeight = $sourceHeight;

        if (max($sourceWidth, $sourceHeight) > $maxDimension) {
            $ratio = $maxDimension / max($sourceWidth, $sourceHeight);
            $newWidth = max(1, (int) round($sourceWidth * $ratio));
            $newHeight = max(1, (int) round($sourceHeight * $ratio));
        }

        $target = $source;

        if ($newWidth !== $sourceWidth || $newHeight !== $sourceHeight) {
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

            imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
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

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($rotated && $rotated !== $image) {
            imagedestroy($image);
        }

        return $rotated ?: $image;
    }
}
