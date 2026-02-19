<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
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
            'Email',
            'Username',
            'Peran',
            'Status',
        ];
    }

    public function map($record): array
    {
        $role = Str::ucfirst((string) $record->getRoleNames()->first());
        $status = $record->is_active ? 'Aktif' : 'Tidak Aktif';

        return [
            $record->name,
            $record->email,
            $record->username,
            $role,
            $status,
        ];
    }
}
