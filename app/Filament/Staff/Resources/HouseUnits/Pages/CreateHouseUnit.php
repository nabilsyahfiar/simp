<?php

namespace App\Filament\Staff\Resources\HouseUnits\Pages;

use App\Filament\Staff\Resources\HouseUnits\HouseUnitResource;
use App\Models\HouseUnit;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateHouseUnit extends CreateRecord
{
    protected static string $resource = HouseUnitResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return HouseUnit::createWithAutoUnitCode($data);
    }
}
