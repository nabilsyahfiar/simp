<?php

namespace App\Filament\Actions;

use App\Exports\HouseUnitsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;

class HouseUnitModalExport
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
                    $records = $query->with(['project', 'assignedForeman'])->get();
                    return Excel::download(new HouseUnitsExport($records), "house-units-{$timestamp}.xlsx");
                }

                if ($format === 'pdf') {
                    $records = $query->with(['project', 'assignedForeman'])->get();
                    $headers = [
                        'Unit', 'Proyek', 'Mandor', 'Status', 'Progres',
                    ];

                    $rows = $records->map(function ($record): array {
                        $percent = $record->official_progress_percent ?? 0;

                        if ($percent <= 0) {
                            $status = 'Belum Mulai';
                        } elseif ($percent >= 100) {
                            $status = 'Selesai';
                        } else {
                            $status = 'Dalam Proses';
                        }

                        return [
                            $record->unit_code,
                            $record->project?->name,
                            $record->assignedForeman?->name,
                            $status,
                            ($percent ?? 0) . '%',
                        ];
                    })->all();

                    $pdf = Pdf::loadView('exports.table', [
                        'title' => "Unit Rumah ($data[start_date] s/d $data[end_date])",
                        'headers' => $headers,
                        'rows' => $rows,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf): void {
                        echo $pdf->output();
                    }, "house-units-{$timestamp}.pdf");
                }
            })
            ->modalWidth('sm')
            ->modalHeading("Ekspor $label")
            ->modalDescription('Pilih rentang waktu unit rumah ditambahkan yang ingin diekspor.');
    }
}
