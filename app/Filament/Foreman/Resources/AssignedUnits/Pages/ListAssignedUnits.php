<?php

namespace App\Filament\Foreman\Resources\AssignedUnits\Pages;

use App\Filament\Foreman\Resources\AssignedUnits\AssignedUnitResource;
use Filament\Resources\Pages\ListRecords;

class ListAssignedUnits extends ListRecords
{
    protected static string $resource = AssignedUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
