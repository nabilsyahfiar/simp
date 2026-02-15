<?php

namespace App\Filament\Resources\StatusAuditLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StatusAuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Details')
                    ->schema([
                        TextInput::make('project_name')
                            ->label('Project')
                            ->dehydrated(false),
                        TextInput::make('unit_code')
                            ->label('Unit')
                            ->dehydrated(false),
                        TextInput::make('changed_by_name')
                            ->label('Changed by')
                            ->dehydrated(false),
                        DateTimePicker::make('changed_at')
                            ->label('Changed at')
                            ->native(false),
                        TextInput::make('old_status_label')
                            ->label('Old status')
                            ->dehydrated(false),
                        TextInput::make('new_status_label')
                            ->label('New status')
                            ->dehydrated(false),
                        TextInput::make('old_percent')
                            ->label('Old progress')
                            ->suffix('%'),
                        TextInput::make('new_percent')
                            ->label('New progress')
                            ->suffix('%'),
                        Textarea::make('note')
                            ->label('Note')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
