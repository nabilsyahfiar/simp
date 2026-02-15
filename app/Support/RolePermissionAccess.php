<?php

namespace App\Support;

use App\Models\RolePermissionSetting;

class RolePermissionAccess
{
    public static function canAccess(string $role, string $moduleKey, string $action): bool
    {
        $user = auth()->user();

        if (! $user?->hasRole($role)) {
            return false;
        }

        return static::isEnabled($role, $moduleKey, $action);
    }

    public static function isEnabled(string $role, string $moduleKey, string $action): bool
    {
        $setting = RolePermissionSetting::query()
            ->where('role', $role)
            ->where('module_key', $moduleKey)
            ->where('action', $action)
            ->first();

        if (! $setting) {
            return RoleAccessConfig::defaultPermission($role, $moduleKey, $action);
        }

        return (bool) $setting->is_enabled;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public static function statesForRole(string $role): array
    {
        $modules = RoleAccessConfig::permissionsForRole($role);

        if ($modules === []) {
            return [];
        }

        $saved = RolePermissionSetting::query()
            ->where('role', $role)
            ->get()
            ->groupBy('module_key')
            ->map(fn ($rows) => $rows->pluck('is_enabled', 'action')->map(fn ($value): bool => (bool) $value)->all())
            ->all();

        $states = [];

        foreach ($modules as $moduleKey => $moduleConfig) {
            $states[$moduleKey] = [];

            foreach ($moduleConfig['actions'] as $action => $default) {
                $states[$moduleKey][$action] = (bool) ($saved[$moduleKey][$action] ?? $default);
            }
        }

        return $states;
    }

    /**
     * @param array<string, array<string, bool>> $states
     */
    public static function saveForRole(string $role, array $states): void
    {
        $modules = RoleAccessConfig::permissionsForRole($role);

        foreach ($modules as $moduleKey => $moduleConfig) {
            foreach ($moduleConfig['actions'] as $action => $default) {
                RolePermissionSetting::query()->updateOrCreate(
                    [
                        'role' => $role,
                        'module_key' => $moduleKey,
                        'action' => $action,
                    ],
                    [
                        'is_enabled' => (bool) ($states[$moduleKey][$action] ?? $default),
                    ],
                );
            }
        }
    }
}
