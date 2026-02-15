<?php

namespace App\Filament\Management\Resources\ProgressReports\Pages;

use App\Filament\Management\Resources\ProgressReports\ProgressReportResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProgressReport extends ViewRecord
{
    protected static string $resource = ProgressReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['project_name'] = $record->unit?->project?->name;
        $data['unit_code'] = $record->unit?->unit_code;
        $data['foreman_name'] = $record->foreman?->name;
        $data['status_label'] = $record->status === 'verified' ? 'Verified' : 'Pending';
        $data['verified_by_name'] = $record->verifiedBy?->name;

        return $data;
    }
}

