<?php

namespace App\Filament\Actions;

use App\Exports\ProjectsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ProjectModalExport
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
                    $records = $query->withCount('houseUnits')->withAvg('houseUnits', 'official_progress_percent')->get();
                    return Excel::download(new ProjectsExport($records), "projects-{$timestamp}.xlsx");
                }

                if ($format === 'pdf') {
                    $records = $query->withCount('houseUnits')->withAvg('houseUnits', 'official_progress_percent')->get();
                    $headers = [
                        'Nama', 'Kode', 'Lokasi', 'Status', 'Progres', 'Tanggal Mulai', 'Unit',
                    ];

                    $rows = $records->map(function ($record): array {
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
                    })->all();

                    $pdf = Pdf::loadView('exports.table', [
                        'title' => "Proyek ($data[start_date] s/d $data[end_date])",
                        'headers' => $headers,
                        'rows' => $rows,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf): void {
                        echo $pdf->output();
                    }, "projects-{$timestamp}.pdf");
                }
            })
            ->modalWidth('sm')
            ->modalHeading("Ekspor $label")
            ->modalDescription('Pilih rentang waktu proyek dibuat yang ingin diekspor.');
    }
}
