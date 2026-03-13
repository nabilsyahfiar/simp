<?php

namespace App\Filament\Actions;

use App\Exports\ProgressReportsExport;
use App\Support\ProgressReportExportFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;

class ProgressReportModalExport
{
    public static function make(): ActionGroup
    {
        return ActionGroup::make([
            self::makeExportAction('xlsx', 'Excel (.xlsx)', 'heroicon-m-table-cells'),
            self::makeExportAction('pdf', 'PDF Standar (.pdf)', 'heroicon-m-document'),
            self::makeExportAction('pdf_thumb', 'PDF + Thumbnail', 'heroicon-m-photo'),
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
                    ->whereBetween('report_date', [$startTime, $endTime]);

                $timestamp = now()->format('Ymd-His');

                if ($format === 'xlsx') {
                    $records = $query->with(['unit.project', 'foreman'])->withCount('photos')->get();
                    return Excel::download(new ProgressReportsExport($records), "progress-reports-{$timestamp}.xlsx");
                }

                if ($format === 'pdf') {
                    $records = $query->with(['unit.project', 'foreman'])->withCount('photos')->get();
                    $headers = [
                        'Tanggal Laporan', 'Proyek', 'Unit', 'Mandor', 
                        'Progres', 'Status', 'Jumlah Foto', 'Ada Foto',
                    ];

                    $rows = $records->map(function ($record): array {
                        $status = ProgressReportExportFormatter::statusLabel($record->status);
                        $photoMeta = ProgressReportExportFormatter::photoSummary($record);

                        return [
                            $record->report_date?->format('Y-m-d H:i'),
                            $record->unit?->project?->name,
                            $record->unit?->unit_code,
                            $record->foreman?->name,
                            ($record->reported_percent ?? 0) . '%',
                            $status,
                            $photoMeta['count'],
                            $photoMeta['has_photo'],
                        ];
                    })->all();

                    $pdf = Pdf::loadView('exports.table', [
                        'title' => "Laporan Progres ($data[start_date] s/d $data[end_date])",
                        'headers' => $headers,
                        'rows' => $rows,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf): void {
                        echo $pdf->output();
                    }, "progress-reports-{$timestamp}.pdf");
                }

                if ($format === 'pdf_thumb') {
                    $records = $query->with(['unit.project', 'foreman', 'photos'])->withCount('photos')->get();

                    $rows = $records->map(function ($record): array {
                        $status = ProgressReportExportFormatter::statusLabel($record->status);
                        $photoMeta = ProgressReportExportFormatter::photoSummary($record);

                        return [
                            'report_date' => $record->report_date?->format('Y-m-d H:i'),
                            'project' => $record->unit?->project?->name,
                            'unit' => $record->unit?->unit_code,
                            'foreman' => $record->foreman?->name,
                            'progress' => ($record->reported_percent ?? 0) . '%',
                            'status' => $status,
                            'photos_count' => $photoMeta['count'],
                            'has_photo' => $photoMeta['has_photo'],
                            'thumbnail_data_uri' => ProgressReportExportFormatter::thumbnailDataUri($record),
                        ];
                    })->all();

                    $pdf = Pdf::loadView('exports.progress-reports-thumbnails', [
                        'title' => "Laporan Progres ($data[start_date] s/d $data[end_date])",
                        'rows' => $rows,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf): void {
                        echo $pdf->output();
                    }, "progress-reports-thumbnails-{$timestamp}.pdf");
                }
            })
            ->modalWidth('sm')
            ->modalHeading("Ekspor $label")
            ->modalDescription('Pilih rentang waktu laporan yang ingin diekspor.');
    }
}
