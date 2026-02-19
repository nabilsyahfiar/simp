<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HouseUnitsExport implements FromCollection, WithHeadings, WithMapping
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
            'Unit',
            'Proyek',
            'Mandor',
            'Status',
            'Progres',
        ];
    }

    public function map($record): array
    {
        $percent = $record->official_progress_percent ?? 0;

        if ($percent <= 0) {
            $status = 'Not started';
        } elseif ($percent >= 100) {
            $status = 'Selesai';
        } else {
            $status = 'In progress';
        }

        return [
            $record->unit_code,
            $record->project?->name,
            $record->assignedForeman?->name,
            $status,
            ($percent ?? 0) . '%',
        ];
    }
}
