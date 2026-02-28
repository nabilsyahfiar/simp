<?php

namespace App\Support;

use App\Models\RoleMenuSetting;

class RoleMenuAccess
{
    /**
     * @var array<string, bool>
     */
    private static array $enabledCache = [];

    /**
     * Default menu map shown in admin toggle table.
     *
     * @return array<string, array<string, string>>
     */
    public static function defaults(): array
    {
        return collect(RoleAccessConfig::roles())
            ->mapWithKeys(fn (array $config, string $role): array => [$role => $config['menus']])
            ->all();
    }

    public static function canAccess(string $role, string $menuKey): bool
    {
        $user = auth()->user();

        if (! $user?->hasRole($role)) {
            return false;
        }

        return static::isEnabled($role, $menuKey);
    }

    public static function isEnabled(string $role, string $menuKey): bool
    {
        $cacheKey = "{$role}|{$menuKey}";

        if (array_key_exists($cacheKey, static::$enabledCache)) {
            return (bool) static::$enabledCache[$cacheKey];
        }

        $setting = RoleMenuSetting::query()
            ->where('role', $role)
            ->where('menu_key', $menuKey)
            ->first();

        if (! $setting) {
            static::$enabledCache[$cacheKey] = true;

            return true;
        }

        $enabled = (bool) $setting->is_enabled;
        static::$enabledCache[$cacheKey] = $enabled;

        return $enabled;
    }

    /**
     * @return array<string, bool>
     */
    public static function statesForRole(string $role): array
    {
        $menus = RoleAccessConfig::menusForRole($role);

        if ($menus === []) {
            return [];
        }

        $saved = RoleMenuSetting::query()
            ->where('role', $role)
            ->pluck('is_enabled', 'menu_key')
            ->all();

        $states = [];

        foreach ($menus as $menuKey => $_label) {
            $states[$menuKey] = array_key_exists($menuKey, $saved) ? (bool) $saved[$menuKey] : true;
        }

        return $states;
    }

    /**
     * @param array<string, bool> $states
     */
    public static function saveForRole(string $role, array $states): void
    {
        $menus = RoleAccessConfig::menusForRole($role);

        foreach ($menus as $menuKey => $menuLabel) {
            RoleMenuSetting::query()->updateOrCreate(
                [
                    'role' => $role,
                    'menu_key' => $menuKey,
                ],
                [
                    'menu_label' => $menuLabel,
                    'is_enabled' => (bool) ($states[$menuKey] ?? true),
                ],
            );
        }

        static::clearCacheForRole($role);
    }

    private static function clearCacheForRole(string $role): void
    {
        static::$enabledCache = collect(static::$enabledCache)
            ->reject(fn ($_value, string $key): bool => str_starts_with($key, "{$role}|"))
            ->all();
    }
}
