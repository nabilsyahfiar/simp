<?php

namespace App\Filament\Management\Resources\HouseUnits\Tables;

use App\Exports\HouseUnitsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class HouseUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_code')
                    ->label('Kode Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Proyek')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('assignedForeman.name')
                    ->label('Mandor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('official_status')
                    ->label('Status')
                    ->badge()
                    ->state(function ($record): string {
                        $percent = $record->official_progress_percent ?? 0;

                        if ($percent <= 0) {
                            return 'Belum Mulai';
                        }

                        if ($percent >= 100) {
                            return 'Selesai';
                        }

                        return 'Dalam Proses';
                    })
                    ->sortable(),
                TextColumn::make('official_progress_percent')
                    ->label('Progres')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Proyek')
                    ->relationship('project', 'name')
                    ->native(false),
                SelectFilter::make('assigned_foreman_id')
                    ->label('Mandor')
                    ->relationship('assignedForeman', 'name', modifyQueryUsing: fn ($query) => $query->role('foreman'))
                    ->native(false),
            ])
            ->recordActions([])
            ->toolbarActions([
                ActionGroup::make([
                    Action::make('export_xlsx')
                        ->label('Excel (.xlsx)')
                        ->icon('heroicon-m-table-cells')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->with(['project', 'assignedForeman'])
                                ->get();
                            $timestamp = now()->format('Ymd-His');

                            return Excel::download(new HouseUnitsExport($records), "house-units-{$timestamp}.xlsx");
                        }),
                    Action::make('export_pdf')
                        ->label('PDF (.pdf)')
                        ->icon('heroicon-m-document')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->with(['project', 'assignedForeman'])
                                ->get();

                            $headers = ['Unit', 'Proyek', 'Mandor', 'Status', 'Progres'];

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

                            $timestamp = now()->format('Ymd-His');

                            $pdf = Pdf::loadView('exports.table', [
                                'title' => 'Unit Rumah',
                                'headers' => $headers,
                                'rows' => $rows,
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(function () use ($pdf): void {
                                echo $pdf->output();
                            }, "house-units-{$timestamp}.pdf");
                        }),
                ])
                    ->label('Ekspor')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),
            ]);
    }
}
