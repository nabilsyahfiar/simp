<?php

namespace App\Filament\Foreman\Resources\AssignedUnits\Tables;

use App\Filament\Foreman\Resources\ProgressReports\ProgressReportResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignedUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_code')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
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
                    }),
                TextColumn::make('official_progress_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('createReport')
                    ->label('Create Report')
                    ->icon('heroicon-m-plus-circle')
                    ->color('info')
                    ->url(fn ($record) => ProgressReportResource::getUrl('create', ['unit_id' => $record->id])),
            ]);
    }
}
