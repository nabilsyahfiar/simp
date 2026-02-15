<?php

namespace App\Filament\Resources\HouseUnits\Pages;

use App\Filament\Resources\HouseUnits\HouseUnitResource;
use App\Models\UnitAssignment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditHouseUnit extends EditRecord
{
    protected static string $resource = HouseUnitResource::class;
    protected ?int $previousForemanId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->previousForemanId = $this->record->assigned_foreman_id;

        return $data;
    }

    protected function afterSave(): void
    {
        if (! Auth::id()) {
            return;
        }

        if ($this->previousForemanId === $this->record->assigned_foreman_id) {
            return;
        }

        UnitAssignment::create([
            'unit_id' => $this->record->id,
            'old_foreman_id' => $this->previousForemanId,
            'new_foreman_id' => $this->record->assigned_foreman_id,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);
    }
}
