<?php

namespace App\Filament\Staff\Resources\HouseUnits\Pages;

use App\Filament\Staff\Resources\HouseUnits\HouseUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHouseUnit extends EditRecord
{
    protected static string $resource = HouseUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
