<?php

namespace App\Filament\Resources\RoleMenuSettings\Tables;

use App\Support\RoleAccessConfig;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoleMenuSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RoleAccessConfig::roleLabels()[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('menu_label')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_enabled')
                    ->label('Aktif')
                    ->onColor('success')
                    ->offColor('danger'),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(RoleAccessConfig::roleLabels())
                    ->native(false),
            ])
            ->defaultSort('role')
            ->paginated([10, 25, 50]);
    }
}
