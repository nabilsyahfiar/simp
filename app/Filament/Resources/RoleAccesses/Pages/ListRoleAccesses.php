<?php

namespace App\Filament\Resources\RoleAccesses\Pages;

use App\Filament\Resources\RoleAccesses\RoleAccessResource;
use Filament\Resources\Pages\ListRecords;

class ListRoleAccesses extends ListRecords
{
    protected static string $resource = RoleAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
