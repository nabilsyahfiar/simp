<?php

namespace App\Filament\Resources\RoleAccesses\Schemas;

use App\Support\RoleAccessConfig;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleAccessForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissionSections = [];

        foreach (RoleAccessConfig::allPermissionModules() as $moduleKey => $moduleLabel) {
            $actions = ['view', 'create', 'edit', 'delete', 'verify'];
            $actionLabels = [
                'view' => 'Lihat',
                'create' => 'Buat',
                'edit' => 'Ubah',
                'delete' => 'Hapus',
                'verify' => 'Verifikasi',
            ];
            $toggles = [];

            foreach ($actions as $action) {
                $toggles[] = Toggle::make("permissions.{$moduleKey}.{$action}")
                    ->label($actionLabels[$action] ?? ucfirst($action))
                    ->disabled(fn ($get): bool => ! (bool) ($get("menus.{$moduleKey}") ?? true))
                    ->visible(function ($get) use ($moduleKey, $action): bool {
                        $role = (string) ($get('name') ?? '');
                        $permissions = RoleAccessConfig::permissionsForRole($role);

                        return isset($permissions[$moduleKey]['actions'][$action]);
                    });
            }

            $permissionSections[] = Section::make($moduleLabel)
                ->afterHeader([
                    Toggle::make("menus.{$moduleKey}")
                        ->label('Menu')
                        ->live()
                        ->visible(fn ($get): bool => array_key_exists($moduleKey, RoleAccessConfig::menusForRole((string) ($get('name') ?? '')))),
                ])
                ->schema($toggles)
                ->columns(5)
                ->columnSpanFull()
                ->visible(fn ($get): bool => array_key_exists($moduleKey, RoleAccessConfig::permissionsForRole((string) ($get('name') ?? ''))));
        }

        return $schema
            ->components([
                Hidden::make('name'),
                Section::make('Akses Izin')
                    ->description('Atur izin aksi untuk setiap modul. Gunakan tombol "Menu" di sisi kanan judul modul untuk menampilkan atau menyembunyikan menu.')
                    ->schema($permissionSections)
                    ->columnSpanFull(),
            ]);
    }
}
