<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Schemas;

use App\Models\HouseUnit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgressReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Laporan')
                    ->schema([
                        Grid::make(2)->schema([
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
                            DateTimePicker::make('report_date')
                                ->label('Tanggal Laporan')
                                ->default(now('Asia/Jakarta'))
                                ->timezone('Asia/Jakarta')
                                ->disabled()
                                ->dehydrated()
                                ->native(false)
                                ->required(),
                        ]),
                    ])->columnSpanFull(),
                Section::make('Kategori Progres Dilaporkan')
                    ->description('Isi persentase (0-100) untuk setiap tahapan. Total keseluruhan akan dihitung otomatis saat disimpan.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('category_progress.pondasi')->label('Pondasi & Sloof')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.bata')->label('Susun Bata & Kolom')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.ring_balok')->label('Ring Balok & Ampig')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.plafon')->label('Plafon & Rangka Atap')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.genteng')->label('Genteng & Nok')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.acian')->label('Acian & Cat')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.keramik')->label('Keramik')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.pintu')->label('Daun Pintu & Kusen')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.listrik')->label('Instalasi Listrik')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                            TextInput::make('category_progress.air')->label('Pengeboran & Air')->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0)->required(),
                        ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(4)
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('photos')
                    ->label('Foto')
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
