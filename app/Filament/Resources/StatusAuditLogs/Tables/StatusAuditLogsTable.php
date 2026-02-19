<?php

namespace App\Filament\Resources\StatusAuditLogs\Tables;

use App\Exports\StatusAuditLogsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class StatusAuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('changed_at')
                    ->label('Waktu Perubahan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('unit.project.name')
                    ->label('Proyek')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('unit.unit_code')
                    ->label('Unit')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('changedBy.name')
                    ->label('Diubah oleh')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('old_status')
                    ->label('Status Lama')
                    ->badge()
                    ->state(fn ($record) => self::mapStatus($record->old_status))
                    ->sortable(),
                TextColumn::make('new_status')
                    ->label('Status Baru')
                    ->badge()
                    ->state(fn ($record) => self::mapStatus($record->new_status))
                    ->sortable(),
                TextColumn::make('old_percent')
                    ->label('Progres Lama')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('new_percent')
                    ->label('Progres Baru')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(40),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'unit_code')
                    ->native(false),
                SelectFilter::make('changed_by')
                    ->label('Diubah oleh')
                    ->relationship('changedBy', 'name')
                    ->native(false),
                SelectFilter::make('new_status')
                    ->label('Status Baru')
                    ->options([
                        'belum_mulai' => 'Not started',
                        'dalam_proses' => 'In progress',
                        'selesai' => 'Selesai',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->color('info'),
            ])
            ->toolbarActions([
                ActionGroup::make([
                    Action::make('export_xlsx')
                        ->label('Excel (.xlsx)')
                        ->icon('heroicon-m-table-cells')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->with(['unit.project', 'changedBy'])
                                ->get();
                            $timestamp = now()->format('Ymd-His');

                            return Excel::download(new StatusAuditLogsExport($records), "status-audit-logs-{$timestamp}.xlsx");
                        }),
                    Action::make('export_pdf')
                        ->label('PDF (.pdf)')
                        ->icon('heroicon-m-document')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->with(['unit.project', 'changedBy'])
                                ->get();

                            $headers = [
                                'Waktu Perubahan',
                                'Proyek',
                                'Unit',
                                'Diubah oleh',
                                'Status Lama',
                                'Status Baru',
                                'Progres Lama',
                                'Progres Baru',
                                'Catatan',
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

                            $timestamp = now()->format('Ymd-His');

                            $pdf = Pdf::loadView('exports.table', [
                                'title' => 'Log Audit Status',
                                'headers' => $headers,
                                'rows' => $rows,
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(function () use ($pdf): void {
                                echo $pdf->output();
                            }, "status-audit-logs-{$timestamp}.pdf");
                        }),
                ])
                    ->label('Ekspor')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),
            ])
            ->defaultSort('changed_at', 'desc');
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








