<?php

namespace App\Filament\Staff\Resources\HouseUnits\Pages;

use App\Filament\Staff\Resources\HouseUnits\HouseUnitResource;
use App\Models\HouseUnit;
use Filament\Resources\Pages\CreateRecord;

class CreateHouseUnit extends CreateRecord
{
    protected static string $resource = HouseUnitResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $projectId = (int) ($data['project_id'] ?? 0);
        abort_unless($projectId > 0, 422);

        $data['unit_code'] = HouseUnit::generateNextUnitCodeForProject($projectId);

        return $data;
    }
}
