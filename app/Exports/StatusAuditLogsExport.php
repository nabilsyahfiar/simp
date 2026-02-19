<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StatusAuditLogsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $records)
    {
    }

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Waktu Perubahan',
            'Proyek',
            'Unit',
            'Diubah oleh',
            'Status Lama',
            'Status Baru',
            'Progres Lama',
            'Progres Baru',
            'Catatan',
        ];
    }

    public function map($record): array
    {
        return [
            $record->changed_at?->format('Y-m-d H:i'),
            $record->unit?->project?->name,
            $record->unit?->unit_code,
            $record->changedBy?->name,
            $this->mapStatus($record->old_status),
            $this->mapStatus($record->new_status),
            ($record->old_percent ?? 0) . '%',
            ($record->new_percent ?? 0) . '%',
            $record->note,
        ];
    }

    private function mapStatus(?string $status): string
    {
        return match ($status) {
            'belum_mulai' => 'Not started',
            'dalam_proses' => 'In progress',
            'selesai' => 'Selesai',
            default => ucfirst((string) $status),
        };
    }
}
