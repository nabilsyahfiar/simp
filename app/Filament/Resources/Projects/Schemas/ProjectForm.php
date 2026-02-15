<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(150),
                TextInput::make('code')
                    ->maxLength(50),
                TextInput::make('location')
                    ->required()
                    ->maxLength(150),
                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required()
                    ->native(false),
                DatePicker::make('start_date')
                    ->label('Start date')
                    ->required()
                    ->native(false),
            ]);
    }
}
