<?php

namespace App\Filament\Management\Resources\HouseUnits\Pages;

use App\Filament\Management\Resources\HouseUnits\HouseUnitResource;
use Filament\Resources\Pages\ListRecords;

class ListHouseUnits extends ListRecords
{
    protected static string $resource = HouseUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

