<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProgressReportsExport implements FromCollection, WithHeadings, WithMapping
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
            'Tanggal Laporan',
            'Proyek',
            'Unit',
            'Mandor',
            'Progres',
            'Status',
            'Jumlah Foto',
            'Ada Foto',
        ];
    }

    public function map($record): array
    {
        $status = $record->status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi';

        return [
            $record->report_date?->format('Y-m-d H:i'),
            $record->unit?->project?->name,
            $record->unit?->unit_code,
            $record->foreman?->name,
            ($record->reported_percent ?? 0) . '%',
            $status,
            (int) ($record->photos_count ?? 0),
            ((int) ($record->photos_count ?? 0) > 0) ? 'Ya' : 'Tidak',
        ];
    }
}
