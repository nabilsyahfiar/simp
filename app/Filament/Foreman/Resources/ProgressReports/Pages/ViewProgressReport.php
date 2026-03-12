<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Pages;

use App\Filament\Foreman\Resources\ProgressReports\ProgressReportResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewProgressReport extends ViewRecord
{
    protected static string $resource = ProgressReportResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Laporan')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('project_name')
                                ->label('Proyek')
                                ->dehydrated(false),
                            TextInput::make('unit_code')
                                ->label('Kode Unit')
                                ->dehydrated(false),
                            TextInput::make('status_label')
                                ->label('Status')
                                ->dehydrated(false),
                            TextInput::make('verified_by_name')
                                ->label('Diverifikasi oleh')
                                ->placeholder('-')
                                ->dehydrated(false),
                            DateTimePicker::make('verified_at')
                                ->label('Tanggal Verifikasi')
                                ->timezone('Asia/Jakarta')
                                ->displayFormat('d M Y H:i')
                                ->native(false),
                            DateTimePicker::make('report_date')
                                ->label('Tanggal Laporan')
                                ->timezone('Asia/Jakarta')
                                ->displayFormat('d M Y H:i')
                                ->native(false),
                            TextInput::make('reported_percent')
                                ->label('Total Progres')
                                ->suffix('%'),
                        ])->columnSpanFull(),
                        Section::make('Kategori Progres Dilaporkan')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('category_progress.pondasi')->label('Pondasi & Sloof')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.bata')->label('Susun Bata & Kolom')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.ring_balok')->label('Ring Balok & Ampig')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.plafon')->label('Plafon & Rangka Atap')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.genteng')->label('Genteng & Nok')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.acian')->label('Acian & Cat')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.keramik')->label('Keramik')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.pintu')->label('Daun Pintu & Kusen')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.listrik')->label('Instalasi Listrik')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                    TextInput::make('category_progress.air')->label('Pengeboran & Air')->numeric()->suffix('%')->disabled()->dehydrated(false),
                                ]),
                            ])
                            ->columnSpanFull()
                            ->collapsible(),
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $data['project_name'] = $record->unit?->project?->name;
        $data['unit_code'] = $record->unit?->unit_code;
        $data['status_label'] = $record->status === 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi';
        $data['verified_by_name'] = $record->verifiedBy?->name;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
