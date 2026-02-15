<?php

namespace App\Filament\Resources\HouseUnits\Schemas;

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
                    ->required()
                    ->native(false),
                TextInput::make('unit_code')
                    ->required()
                    ->maxLength(50)
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
