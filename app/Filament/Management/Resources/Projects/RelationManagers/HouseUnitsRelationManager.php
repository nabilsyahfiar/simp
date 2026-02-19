<?php

namespace App\Filament\Management\Resources\Projects\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HouseUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'houseUnits';

    protected static ?string $title = 'Unit Rumah';

    protected static ?string $label = 'Unit Rumah';

    protected static ?string $pluralLabel = 'Unit Rumah';

    protected static ?string $recordTitleAttribute = 'unit_code';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_code')
                    ->label('Kode Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignedForeman.name')
                    ->label('Mandor')
                    ->searchable()
                    ->sortable(),
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
                    }),
                TextColumn::make('official_progress_percent')
                    ->label('Progres')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([]);
    }
}
