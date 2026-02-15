<?php

namespace App\Filament\Management\Resources\Projects\Pages;

use App\Filament\Management\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

