<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Schemas;

use App\Models\HouseUnit;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\SystemSetting;

class ProgressReportForm
{
    public static function configure(Schema $schema): Schema
    {
        $inputMode = SystemSetting::where('key', 'progress_input_mode')->value('value') ?? 'percentage';

        $categories = [
            'pondasi' => ['label' => 'Pondasi & Sloof', 'weight' => 0.10],
            'bata' => ['label' => 'Susun Bata & Kolom', 'weight' => 0.30],
            'ring_balok' => ['label' => 'Ring Balok & Ampig', 'weight' => 0.20],
            'plafon' => ['label' => 'Plafon & Rangka Atap', 'weight' => 0.10],
            'genteng' => ['label' => 'Genteng & Nok', 'weight' => 0.05],
            'acian' => ['label' => 'Acian & Cat', 'weight' => 0.05],
            'keramik' => ['label' => 'Keramik', 'weight' => 0.05],
            'pintu' => ['label' => 'Daun Pintu & Kusen', 'weight' => 0.05],
            'listrik' => ['label' => 'Instalasi Listrik', 'weight' => 0.05],
            'air' => ['label' => 'Pengeboran & Air', 'weight' => 0.05],
        ];

        $categoryFields = [];

        foreach ($categories as $key => $data) {
            // View-only component (shows weighted progress)
            $categoryFields[] = TextInput::make('category_progress_view.' . $key)
                ->label($data['label'] . ' (' . ($data['weight'] * 100) . '%)')
                ->numeric()
                ->suffix('%')
                ->formatStateUsing(fn ($get) => $get('category_progress.' . $key) !== null ? round($get('category_progress.' . $key) * $data['weight'], 2) : null)
                ->disabled()
                ->dehydrated(false)
                ->visibleOn('view');

            // Edit/Create component (shows actual input mode)
            if ($inputMode === 'checkbox') {
                $categoryFields[] = Checkbox::make('category_progress.' . $key)
                    ->label($data['label'])
                    ->formatStateUsing(fn ($state) => $state >= 100)
                    ->mutateDehydratedStateUsing(fn ($state) => $state ? 100 : 0)
                    ->hiddenOn('view');
            } else {
                $categoryFields[] = TextInput::make('category_progress.' . $key)
                    ->label($data['label'])
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->default(0)
                    ->required()
                    ->hiddenOn('view');
            }
        }

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
                    ->description($inputMode === 'checkbox' ? 'Centang tahapan pekerjaan yang sudah selesai sepenuhnya (100%).' : 'Isi persentase (0-100) untuk setiap tahapan. Total keseluruhan akan dihitung otomatis saat disimpan.')
                    ->schema([
                        Grid::make(2)->schema($categoryFields),
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
