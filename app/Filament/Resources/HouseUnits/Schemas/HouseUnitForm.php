<?php

namespace App\Filament\Resources\HouseUnits\Schemas;

use App\Models\HouseUnit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class HouseUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $operation): void {
                        if ($operation !== 'create') {
                            return;
                        }

                        if (blank($state)) {
                            $set('unit_code', null);

                            return;
                        }

                        $set('unit_code', HouseUnit::generateNextUnitCodeForProject((int) $state));
                    })
                    ->required()
                    ->native(false),
                TextInput::make('unit_code')
                    ->required(fn ($operation): bool => $operation !== 'create')
                    ->maxLength(50)
                    ->disabled(fn ($operation): bool => $operation === 'create')
                    ->dehydrated(fn ($operation): bool => $operation !== 'create')
                    ->helperText('Auto-generated from project code.')
                    ->rules([
                        fn ($get, $record) => Rule::unique('house_units', 'unit_code')
                            ->where('project_id', $get('project_id'))
                            ->ignore($record),
                    ]),
                Select::make('assigned_foreman_id')
                    ->label('Foreman')
                    ->relationship('assignedForeman', 'name', modifyQueryUsing: fn ($query) => $query->role('foreman'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
            ]);
    }
}
