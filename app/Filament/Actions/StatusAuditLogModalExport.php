<?php

namespace App\Filament\Actions;

use App\Exports\StatusAuditLogsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Maatwebsite\Excel\Facades\Excel;

class StatusAuditLogModalExport
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
                    ->whereBetween('changed_at', [$startTime, $endTime]);

                $timestamp = now()->format('Ymd-His');

                if ($format === 'xlsx') {
                    $records = $query->with(['unit.project', 'changedBy'])->get();
                    return Excel::download(new StatusAuditLogsExport($records), "status-audit-logs-{$timestamp}.xlsx");
                }

                if ($format === 'pdf') {
                    $records = $query->with(['unit.project', 'changedBy'])->get();
                    $headers = [
                        'Waktu Perubahan', 'Proyek', 'Unit', 'Diubah oleh', 'Status Lama', 'Status Baru', 'Progres Lama', 'Progres Baru', 'Catatan',
                    ];

                    $rows = $records->map(function ($record): array {
                        return [
                            $record->changed_at?->format('Y-m-d H:i'),
                            $record->unit?->project?->name,
                            $record->unit?->unit_code,
                            $record->changedBy?->name,
                            self::mapStatus($record->old_status),
                            self::mapStatus($record->new_status),
                            ($record->old_percent ?? 0) . '%',
                            ($record->new_percent ?? 0) . '%',
                            $record->note,
                        ];
                    })->all();

                    $pdf = Pdf::loadView('exports.table', [
                        'title' => "Log Audit Status ($data[start_date] s/d $data[end_date])",
                        'headers' => $headers,
                        'rows' => $rows,
                    ])->setPaper('a4', 'landscape');

                    return response()->streamDownload(function () use ($pdf): void {
                        echo $pdf->output();
                    }, "status-audit-logs-{$timestamp}.pdf");
                }
            })
            ->modalWidth('sm')
            ->modalHeading("Ekspor $label")
            ->modalDescription('Pilih rentang waktu perubahan log yang ingin diekspor.');
    }

    private static function mapStatus(?string $status): string
    {
        return match ($status) {
            'belum_mulai' => 'Not started',
            'dalam_proses' => 'In progress',
            'selesai' => 'Selesai',
            default => ucfirst((string) $status),
        };
    }
}
