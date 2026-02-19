<?php

namespace App\Filament\Resources\HouseUnits\Pages;

use App\Filament\Resources\HouseUnits\HouseUnitResource;
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
