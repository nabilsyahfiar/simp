<?php

namespace App\Support;

use App\Models\ProgressReport;
use Illuminate\Support\Facades\Storage;

class ProgressReportExportFormatter
{
    public static function statusLabel(?string $status): string
    {
        return $status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi';
    }

    public static function hasPhotoLabel(int $photosCount): string
    {
        return $photosCount > 0 ? 'Ya' : 'Tidak';
    }

    /**
     * @return array{count: int, has_photo: string}
     */
    public static function photoSummary(ProgressReport $record): array
    {
        $photosCount = (int) ($record->photos_count ?? 0);

        return [
            'count' => $photosCount,
            'has_photo' => static::hasPhotoLabel($photosCount),
        ];
    }

    public static function thumbnailDataUri(ProgressReport $record): ?string
    {
        return static::firstPhotoDataUri($record);
    }

    private static function firstPhotoDataUri(ProgressReport $record): ?string
    {
        $firstPhotoPath = $record->photos->first()?->file_path;

        if (! $firstPhotoPath) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($firstPhotoPath)) {
            return null;
        }

        $mimeType = (string) ($disk->mimeType($firstPhotoPath) ?: 'image/jpeg');
        $content = $disk->get($firstPhotoPath);

        return 'data:' . $mimeType . ';base64,' . base64_encode($content);
    }
}
