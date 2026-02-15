<?php

namespace App\Filament\Resources\RoleMenuSettings\Pages;

use App\Filament\Resources\RoleMenuSettings\RoleMenuSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListRoleMenuSettings extends ListRecords
{
    protected static string $resource = RoleMenuSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
