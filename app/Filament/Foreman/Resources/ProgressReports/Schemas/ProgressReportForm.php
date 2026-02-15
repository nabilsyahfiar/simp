<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Schemas;

use App\Models\HouseUnit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProgressReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('unit_id')
                    ->label('Unit')
                    ->options(function (): array {
                        return HouseUnit::query()
                            ->where('assigned_foreman_id', auth()->id())
                            ->orderBy('unit_code')
                            ->pluck('unit_code', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                TextInput::make('reported_percent')
                    ->label('Reported progress')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                DateTimePicker::make('report_date')
                    ->label('Report date')
                    ->default(now('Asia/Jakarta'))
                    ->timezone('Asia/Jakarta')
                    ->disabled()
                    ->dehydrated()
                    ->native(false)
                    ->required(),
                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('photos')
                    ->label('Photos')
                    ->disk('public')
                    ->directory('progress-reports')
                    ->image()
                    ->imagePreviewHeight('180')
                    ->multiple()
                    ->required()
                    ->minFiles(1)
                    ->maxFiles(5)
                    ->maxSize(8192)
                    ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                    ->columnSpanFull(),
            ]);
    }
}
