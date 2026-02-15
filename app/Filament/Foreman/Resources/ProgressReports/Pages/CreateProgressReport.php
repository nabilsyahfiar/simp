<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Pages;

use App\Filament\Foreman\Resources\ProgressReports\ProgressReportResource;
use App\Models\HouseUnit;
use App\Models\ProgressReport;
use App\Models\ReportPhoto;
use App\Support\ImageCompressor;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CreateProgressReport extends CreateRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterFill(): void
    {
        $requestedUnitId = request()->query('unit_id');

        if (! $requestedUnitId) {
            return;
        }

        $isAssigned = HouseUnit::query()
            ->whereKey($requestedUnitId)
            ->where('assigned_foreman_id', auth()->id())
            ->exists();

        if (! $isAssigned) {
            return;
        }

        $this->form->fill([
            ...($this->data ?? []),
            'unit_id' => (int) $requestedUnitId,
            'report_date' => now('Asia/Jakarta'),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $assignedUnit = HouseUnit::query()
            ->whereKey($data['unit_id'] ?? null)
            ->where('assigned_foreman_id', auth()->id())
            ->exists();

        abort_unless($assignedUnit, 403);

        $data['foreman_id'] = auth()->id();
        $data['status'] = 'pending';
        $data['report_date'] = now('Asia/Jakarta');

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $photos = $data['photos'] ?? [];
        unset($data['photos']);

        /** @var ProgressReport $record */
        $record = parent::handleRecordCreation($data);

        $this->syncPhotos($record, $photos);

        return $record;
    }

    private function syncPhotos(ProgressReport $report, array $paths): void
    {
        $paths = array_values(array_filter($paths));
        $maxDimension = (int) config('simpro.image.max_dimension', 1920);
        $jpegQuality = (int) config('simpro.image.jpeg_quality', 82);
        $pngCompression = (int) config('simpro.image.png_compression', 7);

        foreach ($paths as $path) {
            ImageCompressor::compressPublicImage($path, $maxDimension, $jpegQuality, $pngCompression);

            ReportPhoto::create([
                'report_id' => $report->id,
                'file_path' => $path,
                'file_size' => Storage::disk('public')->size($path) ?: 0,
                'mime_type' => Storage::disk('public')->mimeType($path) ?: 'image/jpeg',
                'created_at' => now('Asia/Jakarta'),
            ]);
        }
    }
}
