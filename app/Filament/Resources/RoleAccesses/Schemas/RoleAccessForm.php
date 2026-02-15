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
            $toggles = [];

            foreach ($actions as $action) {
                $toggles[] = Toggle::make("permissions.{$moduleKey}.{$action}")
                    ->label(ucfirst($action))
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
                Section::make('Permission Access')
                    ->description('Toggle action permissions for each module. Switch "Menu" on/off at the right side of each module title.')
                    ->schema($permissionSections)
                    ->columnSpanFull(),
            ]);
    }
}
