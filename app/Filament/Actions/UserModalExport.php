<?php

namespace App\Filament\Actions;

use App\Exports\UsersExport;
use App\Support\RoleAccessConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;

class UserModalExport
{
    public static function make(): ActionGroup
    {
        return ActionGroup::make([
            self::makeExportAction('xlsx', 'Excel (.xlsx)', 'heroicon-m-table-cells'),
            self::makeExportAction('pdf', 'PDF Standar (.pdf)', 'heroicon-m-document'),
        ])
            ->label('Ekspor')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->button();
    }

    protected static function makeExportAction(string $format, string $label, string $icon): Action
    {
        return Action::make('export_' . $format)
            ->label($label)
            ->icon($icon)
            ->form([
                DatePicker::make('start_date')
                    ->label('Dari Tanggal')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection()
                    ->maxDate(now()),
                DatePicker::make('end_date')
                    ->label('Sampai Tanggal')
                    ->required()
                    ->native(false)
                    ->closeOnDateSelection()
                    ->afterOrEqual('start_date')
                    ->maxDate(now()),
            ])
            ->action(function (array $data, $livewire) use ($format) {
                $startTime = $data['start_date'] . ' 00:00:00';
                $endTime = $data['end_date'] . ' 23:59:59';

                $query = $livewire->getTableQueryForExport()
                    ->whereBetween('created_at', [$startTime, $endTime]);

                $timestamp = now()->format('Ymd-His');

                if ($format === 'xlsx') {
                    $records = $query->with('roles')->get();
                    return Excel::download(new UsersExport($records), "users-{$timestamp}.xlsx");
                }

                if ($format === 'pdf') {
                    $records = $query->with('roles')->get();
                    $headers = [
                        'Nama', 'Email', 'Nama Pengguna', 'Peran', 'Status',
                    ];

                    $rows = $records->map(function ($record): array {
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
                    })->all();

                    $pdf = Pdf::loadView('exports.table', [
                        'title' => "Pengguna ($data[start_date] s/d $data[end_date])",
                        'headers' => $headers,
                        'rows' => $rows,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf): void {
                        echo $pdf->output();
                    }, "users-{$timestamp}.pdf");
                }
            })
            ->modalWidth('sm')
            ->modalHeading("Ekspor $label")
            ->modalDescription('Pilih rentang waktu pengguna didaftarkan yang ingin diekspor.');
    }
}
