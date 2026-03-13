<?php

namespace App\Filament\Management\Resources\ProgressReports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
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
            $categoryFields[] = TextInput::make('category_progress.' . $key)
                ->label($data['label'] . ' (' . ($data['weight'] * 100) . '%)')
                ->numeric()
                ->suffix('%')
                ->formatStateUsing(fn ($state) => $state !== null ? round($state * $data['weight'], 2) : null)
                ->disabled()
                ->dehydrated(false)
                ->required();
        }

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
                            ->label('Total Progres')
                            ->disabled()
                            ->dehydrated(false)
                            ->suffix('%'),
                        Section::make('Kategori Progres Dilaporkan')
                            ->schema([
                                Grid::make(2)->schema($categoryFields),
                            ])
                            ->columnSpanFull()
                            ->collapsible(),
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

