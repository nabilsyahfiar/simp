<?php

namespace App\Filament\Management\Resources\ProgressReports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgressReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Laporan')
                    ->schema([
                        TextInput::make('project_name')
                            ->label('Proyek')
                            ->dehydrated(false),
                        TextInput::make('unit_code')
                            ->label('Kode Unit')
                            ->dehydrated(false),
                        TextInput::make('foreman_name')
                            ->label('Mandor')
                            ->dehydrated(false),
                        TextInput::make('status_label')
                            ->label('Status')
                            ->dehydrated(false),
                        TextInput::make('verified_by_name')
                            ->label('Diverifikasi oleh')
                            ->dehydrated(false)
                            ->placeholder('-'),
                        DateTimePicker::make('verified_at')
                            ->label('Tanggal Verifikasi')
                            ->timezone('Asia/Jakarta')
                            ->displayFormat('d M Y H:i')
                            ->native(false),
                        TextInput::make('reported_percent')
                            ->label('Progres Dilaporkan')
                            ->suffix('%'),
                        DateTimePicker::make('report_date')
                            ->label('Tanggal Laporan')
                            ->timezone('Asia/Jakarta')
                            ->displayFormat('d M Y H:i')
                            ->native(false),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Foto')
                    ->schema([
                        ViewField::make('photos_preview')
                            ->view('filament.resources.progress-reports.components.photos')
                            ->dehydrated(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}

