<?php

namespace App\Filament\Resources\HouseUnits\Pages;

use App\Filament\Resources\HouseUnits\HouseUnitResource;
use App\Models\HouseUnit;
use App\Models\UnitAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;

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

    protected function afterCreate(): void
    {
        if (! Auth::id()) {
            return;
        }

        UnitAssignment::create([
            'unit_id' => $this->record->id,
            'old_foreman_id' => null,
            'new_foreman_id' => $this->record->assigned_foreman_id,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);
    }
}
