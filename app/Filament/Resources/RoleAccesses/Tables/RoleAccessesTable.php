<?php

namespace App\Filament\Resources\RoleAccesses\Tables;

use App\Support\RoleAccessConfig;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoleAccessesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RoleAccessConfig::roleLabels()[$state] ?? ucfirst($state))
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Manage Access')->icon('heroicon-m-cog-6-tooth')->color('info'),
            ])
            ->defaultSort('name')
            ->paginated(false);
    }
}
