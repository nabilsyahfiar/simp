<?php

namespace App\Filament\Resources\RoleAccesses\Pages;

use App\Filament\Resources\RoleAccesses\RoleAccessResource;
use App\Support\RoleAccessConfig;
use App\Support\RoleMenuAccess;
use App\Support\RolePermissionAccess;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditRoleAccess extends EditRecord
{
    protected static string $resource = RoleAccessResource::class;

    public function getHeading(): string
    {
        $role = (string) ($this->record?->name ?? '');
        $label = RoleAccessConfig::roleLabels()[$role] ?? $role;

        return "Ubah Peran {$label}";
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $role = $this->record->name;

        $data['menus'] = RoleMenuAccess::statesForRole($role);
        $data['permissions'] = RolePermissionAccess::statesForRole($role);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $role = $record->name;

        RoleMenuAccess::saveForRole($role, $data['menus'] ?? []);
        RolePermissionAccess::saveForRole($role, $data['permissions'] ?? []);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan perubahan'),
            Action::make('cancel')
                ->label('Batal')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }
}
