<?php

namespace App\Filament\Resources\StatusAuditLogs\Pages;

use App\Filament\Resources\StatusAuditLogs\StatusAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListStatusAuditLogs extends ListRecords
{
    protected static string $resource = StatusAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
