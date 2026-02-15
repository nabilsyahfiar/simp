<?php

use App\Support\RoleAccessConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (RoleAccessConfig::roles() as $role => $roleConfig) {
            foreach ($roleConfig['menus'] as $menuKey => $menuLabel) {
                DB::table('role_menu_settings')->updateOrInsert(
                    [
                        'role' => $role,
                        'menu_key' => $menuKey,
                    ],
                    [
                        'menu_label' => $menuLabel,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }
    }

    public function down(): void
    {
        // Intentionally no-op to avoid deleting user-managed role menu settings.
    }
};
