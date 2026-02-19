<?php

namespace App\Filament\Staff\Resources\HouseUnits\Pages;

use App\Filament\Staff\Resources\HouseUnits\HouseUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListHouseUnits extends ListRecords
{
    protected static string $resource = HouseUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat ' . Str::title(static::getResource()::getModelLabel()))
                ->icon('heroicon-m-plus'),
        ];
    }
}
