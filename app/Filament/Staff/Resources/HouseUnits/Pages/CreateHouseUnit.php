<?php

namespace App\Filament\Staff\Resources\HouseUnits\Pages;

use App\Filament\Staff\Resources\HouseUnits\HouseUnitResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHouseUnit extends CreateRecord
{
    protected static string $resource = HouseUnitResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
