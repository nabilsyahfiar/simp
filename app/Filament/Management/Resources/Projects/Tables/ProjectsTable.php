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
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'inactive' ? 'Tidak Aktif' : 'Aktif')
                    ->color(fn (?string $state): string => strtolower($state) === 'inactive' ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('house_units_avg_official_progress_percent')
                    ->label('Progres')
                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 0) . '%')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('house_units_count')
                    ->counts('houseUnits')
                    ->label('Unit')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()->color('primary'),
            ])
            ->toolbarActions([
                \App\Filament\Actions\ProjectModalExport::make(),
            ]);
    }
}
