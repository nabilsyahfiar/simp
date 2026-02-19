<?php

namespace App\Filament\Foreman\Resources\ProgressReports\Pages;

use App\Filament\Foreman\Resources\ProgressReports\ProgressReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListProgressReports extends ListRecords
{
    protected static string $resource = ProgressReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat ' . Str::title(static::getResource()::getModelLabel()))
                ->icon('heroicon-m-plus'),
        ];
    }
}
