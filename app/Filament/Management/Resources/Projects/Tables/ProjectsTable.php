<?php

namespace App\Filament\Management\Resources\Projects\Tables;

use App\Exports\ProjectsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withAvg('houseUnits', 'official_progress_percent'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'inactive' ? 'Inactive' : 'Active')
                    ->sortable(),
                TextColumn::make('house_units_avg_official_progress_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0) . '%')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Start date')
                    ->date()
                    ->sortable(),
                TextColumn::make('house_units_count')
                    ->counts('houseUnits')
                    ->label('Units')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
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
                                ->withCount('houseUnits')
                                ->withAvg('houseUnits', 'official_progress_percent')
                                ->get();
                            $timestamp = now()->format('Ymd-His');

                            return Excel::download(new ProjectsExport($records), "projects-{$timestamp}.xlsx");
                        }),
                    Action::make('export_pdf')
                        ->label('PDF (.pdf)')
                        ->icon('heroicon-m-document')
                        ->action(function ($livewire) {
                            $records = $livewire->getTableQueryForExport()
                                ->withCount('houseUnits')
                                ->withAvg('houseUnits', 'official_progress_percent')
                                ->get();

                            $headers = [
                                'Name',
                                'Location',
                                'Status',
                                'Progress',
                                'Start date',
                                'Units',
                            ];

                            $rows = $records->map(function ($record): array {
                                $status = $record->status === 'inactive' ? 'Inactive' : 'Active';
                                $progress = number_format((float) ($record->house_units_avg_official_progress_percent ?? 0), 0) . '%';
                                $startDate = $record->start_date ? Carbon::parse($record->start_date)->format('Y-m-d') : null;

                                return [
                                    $record->name,
                                    $record->location,
                                    $status,
                                    $progress,
                                    $startDate,
                                    $record->house_units_count ?? 0,
                                ];
                            })->all();

                            $timestamp = now()->format('Ymd-His');

                            $pdf = Pdf::loadView('exports.table', [
                                'title' => 'Projects',
                                'headers' => $headers,
                                'rows' => $rows,
                            ])->setPaper('a4', 'landscape');

                            return response()->streamDownload(function () use ($pdf): void {
                                echo $pdf->output();
                            }, "projects-{$timestamp}.pdf");
                        }),
                ])
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->button(),
            ]);
    }
}

