<?php

namespace App\Filament\Management\Resources\Projects\Pages;

use App\Filament\Management\Resources\Projects\ProjectResource;
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
                TextInput::make('name')->label('Name'),
                TextInput::make('code')->label('Code'),
                TextInput::make('location')->label('Location'),
                TextInput::make('status_label')
                    ->label('Status')
                    ->dehydrated(false),
                TextInput::make('progress_average')
                    ->label('Progress')
                    ->dehydrated(false),
                DatePicker::make('start_date')
                    ->label('Start date')
                    ->dehydrated(false)
                    ->native(false),
                Textarea::make('description')
                    ->label('Description')
                    ->dehydrated(false)
                    ->columnSpanFull(),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $average = (float) ($record->houseUnits()->avg('official_progress_percent') ?? 0);

        $data['status_label'] = $record->status === 'inactive' ? 'Inactive' : 'Active';
        $data['progress_average'] = number_format($average, 0) . '%';

        return $data;
    }
}

