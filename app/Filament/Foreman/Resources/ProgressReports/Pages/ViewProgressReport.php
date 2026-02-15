<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Pages;

use App\Filament\Foreman\Resources\ProgressReports\ProgressReportResource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewProgressReport extends ViewRecord
{
    protected static string $resource = ProgressReportResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Details')
                    ->schema([
                        TextInput::make('project_name')
                            ->label('Project')
                            ->dehydrated(false),
                        TextInput::make('unit_code')
                            ->label('Unit')
                            ->dehydrated(false),
                        TextInput::make('status_label')
                            ->label('Status')
                            ->dehydrated(false),
                        TextInput::make('verified_by_name')
                            ->label('Verified by')
                            ->placeholder('-')
                            ->dehydrated(false),
                        DateTimePicker::make('verified_at')
                            ->label('Verified at')
                            ->timezone('Asia/Jakarta')
                            ->displayFormat('d M Y H:i')
                            ->native(false),
                        TextInput::make('reported_percent')
                            ->label('Reported progress')
                            ->suffix('%'),
                        DateTimePicker::make('report_date')
                            ->label('Report date')
                            ->timezone('Asia/Jakarta')
                            ->displayFormat('d M Y H:i')
                            ->native(false),
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Photos')
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
        $data['status_label'] = $record->status === 'verified' ? 'Verified' : 'Pending';
        $data['verified_by_name'] = $record->verifiedBy?->name;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
