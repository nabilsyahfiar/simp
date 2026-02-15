<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Tables;

use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgressReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_date')
                    ->label('Report date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('unit.unit_code')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reported_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => ($state ?? 0) . '%')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->state(fn ($record) => $record->status === 'verified' ? 'Verified' : 'Pending')
                    ->color(fn (string $state) => $state === 'Verified' ? 'success' : 'warning')
                    ->sortable(),
                TextColumn::make('verifiedBy.name')
                    ->label('Verified by')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship(
                        'unit',
                        'unit_code',
                        modifyQueryUsing: fn (Builder $query) => $query->where('assigned_foreman_id', auth()->id())
                    )
                    ->native(false),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->color('info'),
                EditAction::make()
                    ->visible(fn ($record): bool => $record->status === 'pending'),
            ])
            ->defaultSort('report_date', 'desc');
    }
}
