<?php

namespace App\Filament\Resources\HouseUnits\Tables;

use App\Exports\HouseUnitsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('assignedForeman.name')
                    ->label('Foreman')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('official_status')
                    ->label('Status')
                    ->badge()
                    ->state(function ($record): string {
                        $percent = $record->official_progress_percent ?? 0;

                        if ($percent <= 0) {
                            return 'Not started';
                        }

                        if ($percent >= 100) {
                            return 'Completed';
                        }

                        return 'In progress';
                    })
                    ->sortable(),
                TextColumn::make('official_progress_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name')
                    ->native(false),
                SelectFilter::make('assigned_foreman_id')
                    ->label('Foreman')
                    ->relationship('assignedForeman', 'name', modifyQueryUsing: fn ($query) => $query->role('foreman'))
                    ->native(false),
                SelectFilter::make('official_status')
                    ->label('Status')
                    ->options([
                        'belum_mulai' => 'Not started',
                        'dalam_proses' => 'In progress',
                        'selesai' => 'Completed',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
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

                            $headers = [
                                'Unit',
                                'Project',
                                'Foreman',
                                'Status',
                                'Progress',
                            ];

                            $rows = $records->map(function ($record): array {
                                $percent = $record->official_progress_percent ?? 0;

                                if ($percent <= 0) {
                                    $status = 'Not started';
                                } elseif ($percent >= 100) {
                                    $status = 'Completed';
                                } else {
                                    $status = 'In progress';
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
                                'title' => 'House Units',
                                'headers' => $headers,
                                'rows' => $rows,
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(function () use ($pdf): void {
                                echo $pdf->output();
                            }, "house-units-{$timestamp}.pdf");
                        }),
                ])
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),
            ]);
    }
}








