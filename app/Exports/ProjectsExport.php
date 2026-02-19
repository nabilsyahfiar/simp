<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping
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
            'Nama',
            'Kode',
            'Lokasi',
            'Status',
            'Progres',
            'Tanggal Mulai',
            'Unit',
        ];
    }

    public function map($record): array
    {
        $status = $record->status === 'inactive' ? 'Tidak Aktif' : 'Aktif';
        $progress = number_format((float) ($record->house_units_avg_official_progress_percent ?? 0), 0) . '%';
        $startDate = $record->start_date
            ? Carbon::parse($record->start_date)->format('Y-m-d')
            : null;

        return [
            $record->name,
            $record->code,
            $record->location,
            $status,
            $progress,
            $startDate,
            $record->house_units_count ?? 0,
        ];
    }
}
