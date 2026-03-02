<?php

namespace App\Exports;

use App\Support\RoleAccessConfig;
use Illuminate\Support\Collection;
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
            'Nama Pengguna',
            'Peran',
            'Status',
        ];
    }

    public function map($record): array
    {
        $roleKey = (string) $record->getRoleNames()->first();
        $role = RoleAccessConfig::roleLabels()[$roleKey] ?? $roleKey;
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
