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
                Section::make('Detail Audit')
                    ->schema([
                        TextInput::make('project_name')
                            ->label('Proyek')
                            ->dehydrated(false),
                        TextInput::make('unit_code')
                            ->label('Kode Unit')
                            ->dehydrated(false),
                        TextInput::make('changed_by_name')
                            ->label('Diubah oleh')
                            ->dehydrated(false),
                        DateTimePicker::make('changed_at')
                            ->label('Waktu Perubahan')
                            ->native(false),
                        TextInput::make('old_status_label')
                            ->label('Status Lama')
                            ->dehydrated(false),
                        TextInput::make('new_status_label')
                            ->label('Status Baru')
                            ->dehydrated(false),
                        TextInput::make('old_percent')
                            ->label('Progres Lama')
                            ->suffix('%'),
                        TextInput::make('new_percent')
                            ->label('Progres Baru')
                            ->suffix('%'),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
