<?php

namespace App\Filament\Management\Resources\ProgressReports\Pages;

use App\Filament\Management\Resources\ProgressReports\ProgressReportResource;
use Filament\Resources\Pages\ListRecords;

class ListProgressReports extends ListRecords
{
    protected static string $resource = ProgressReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

