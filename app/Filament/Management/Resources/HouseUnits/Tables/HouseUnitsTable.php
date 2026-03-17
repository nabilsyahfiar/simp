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
                    ->color(function ($record): string {
                        $p = $record->official_progress_percent ?? 0;
                        if ($p <= 0) return 'danger';
                        if ($p >= 100) return 'success';
                        return 'warning';
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
                \App\Filament\Actions\HouseUnitModalExport::make(),
            ]);
    }
}
