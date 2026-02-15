<?php

namespace App\Filament\Resources\StatusAuditLogs\Pages;

use App\Filament\Resources\StatusAuditLogs\StatusAuditLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewStatusAuditLog extends ViewRecord
{
    protected static string $resource = StatusAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['project_name'] = $record->unit?->project?->name;
        $data['unit_code'] = $record->unit?->unit_code;
        $data['changed_by_name'] = $record->changedBy?->name;
        $data['old_status_label'] = $this->mapStatus($record->old_status);
        $data['new_status_label'] = $this->mapStatus($record->new_status);

        return $data;
    }

    private function mapStatus(?string $status): string
    {
        return match ($status) {
            'belum_mulai' => 'Not started',
            'dalam_proses' => 'In progress',
            'selesai' => 'Completed',
            default => ucfirst((string) $status),
        };
    }
}
