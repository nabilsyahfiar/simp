<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Pages;

use App\Filament\Foreman\Resources\ProgressReports\ProgressReportResource;
use App\Models\HouseUnit;
use App\Models\ProgressReport;
use App\Models\ReportPhoto;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditProgressReport extends EditRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['photos'] = $this->getRecord()->photos()->pluck('file_path')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless($this->getRecord()->status === 'pending', 403);

        $assignedUnit = HouseUnit::query()
            ->whereKey($data['unit_id'] ?? null)
            ->where('assigned_foreman_id', auth()->id())
            ->exists();

        abort_unless($assignedUnit, 403);

        $data['foreman_id'] = $this->getRecord()->foreman_id;
        $data['status'] = $this->getRecord()->status;
        $data['report_date'] = $this->getRecord()->report_date;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $photos = $data['photos'] ?? [];
        unset($data['photos']);

        /** @var ProgressReport $record */
        $record = parent::handleRecordUpdate($record, $data);

        $this->syncPhotos($record, $photos);

        return $record;
    }

    private function syncPhotos(ProgressReport $report, array $incomingPaths): void
    {
        $incomingPaths = array_values(array_filter($incomingPaths));

        $currentPaths = $report->photos()->pluck('file_path')->all();

        $toDelete = array_diff($currentPaths, $incomingPaths);
        $toAdd = array_diff($incomingPaths, $currentPaths);

        if (! empty($toDelete)) {
            $report->photos()->whereIn('file_path', $toDelete)->delete();
            Storage::disk('public')->delete($toDelete);
        }

        foreach ($toAdd as $path) {
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
