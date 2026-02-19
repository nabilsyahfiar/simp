<?php

namespace App\Support;

class RoleAccessConfig
{
    /**
     * @return array<string, array{label: string, menus: array<string, string>, permissions: array<string, array{label: string, actions: array<string, bool>}>}>
     */
    public static function roles(): array
    {
        return [
            'admin' => [
                'label' => 'Admin',
                'menus' => [
                    'users' => 'Pengguna',
                    'projects' => 'Proyek',
                    'house_units' => 'Unit Rumah',
                    'progress_reports' => 'Laporan Progres',
                    'status_audit_logs' => 'Log Audit Status',
                ],
                'permissions' => [
                    'users' => [
                        'label' => 'Pengguna',
                        'actions' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                    ],
                    'projects' => [
                        'label' => 'Proyek',
                        'actions' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                    ],
                    'house_units' => [
                        'label' => 'Unit Rumah',
                        'actions' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                    ],
                    'progress_reports' => [
                        'label' => 'Laporan Progres',
                        'actions' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                    ],
                    'status_audit_logs' => [
                        'label' => 'Log Audit Status',
                        'actions' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                    ],
                ],
            ],
            'staff' => [
                'label' => 'Staf',
                'menus' => [
                    'projects' => 'Proyek',
                    'house_units' => 'Unit Rumah',
                    'progress_reports' => 'Laporan Progres',
                ],
                'permissions' => [
                    'projects' => [
                        'label' => 'Proyek',
                        'actions' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                    ],
                    'house_units' => [
                        'label' => 'Unit Rumah',
                        'actions' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                    ],
                    'progress_reports' => [
                        'label' => 'Laporan Progres',
                        'actions' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'verify' => true],
                    ],
                ],
            ],
            'foreman' => [
                'label' => 'Mandor',
                'menus' => [
                    'assigned_units' => 'Unit Tugas',
                    'progress_reports' => 'Laporan Progres',
                ],
                'permissions' => [
                    'assigned_units' => [
                        'label' => 'Unit Tugas',
                        'actions' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                    ],
                    'progress_reports' => [
                        'label' => 'Laporan Progres',
                        'actions' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false],
                    ],
                ],
            ],
            'management' => [
                'label' => 'Manajemen',
                'menus' => [
                    'projects' => 'Proyek',
                    'house_units' => 'Unit Rumah',
                    'progress_reports' => 'Laporan Progres',
                ],
                'permissions' => [
                    'projects' => [
                        'label' => 'Proyek',
                        'actions' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                    ],
                    'house_units' => [
                        'label' => 'Unit Rumah',
                        'actions' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                    ],
                    'progress_reports' => [
                        'label' => 'Laporan Progres',
                        'actions' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function roleLabels(): array
    {
        return collect(static::roles())
            ->mapWithKeys(fn (array $config, string $role): array => [$role => $config['label']])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function menusForRole(string $role): array
    {
        return static::roles()[$role]['menus'] ?? [];
    }

    /**
     * @return array<string, array{label: string, actions: array<string, bool>}>
     */
    public static function permissionsForRole(string $role): array
    {
        return static::roles()[$role]['permissions'] ?? [];
    }

    /**
     * @return array<string, string>
     */
    public static function allMenus(): array
    {
        return collect(static::roles())
            ->flatMap(fn (array $config) => $config['menus'])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function allPermissionModules(): array
    {
        return collect(static::roles())
            ->flatMap(function (array $config): array {
                return collect($config['permissions'])
                    ->mapWithKeys(fn (array $moduleConfig, string $moduleKey): array => [$moduleKey => $moduleConfig['label']])
                    ->all();
            })
            ->all();
    }

    public static function defaultPermission(string $role, string $moduleKey, string $action): bool
    {
        return (bool) (static::permissionsForRole($role)[$moduleKey]['actions'][$action] ?? false);
    }
}
