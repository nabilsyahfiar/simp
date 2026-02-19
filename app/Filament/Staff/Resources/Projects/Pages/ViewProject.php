<?php

namespace App\Filament\Staff\Resources\Projects\Pages;

use App\Filament\Staff\Resources\Projects\ProjectResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama'),
                TextInput::make('code')
                    ->label('Kode'),
                TextInput::make('location')
                    ->label('Lokasi'),
                TextInput::make('status_label')
                    ->label('Status')
                    ->dehydrated(false),
                TextInput::make('progress_average')
                    ->label('Progres')
                    ->dehydrated(false),
                DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->dehydrated(false)
                    ->native(false),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $average = (float) ($record->houseUnits()->avg('official_progress_percent') ?? 0);

        $data['status_label'] = $record->status === 'inactive' ? 'Tidak Aktif' : 'Aktif';
        $data['progress_average'] = number_format($average, 0) . '%';

        return $data;
    }
}
