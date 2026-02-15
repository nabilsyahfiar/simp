<?php

use App\Support\RoleAccessConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission_settings', function (Blueprint $table) {
            $table->id();
            $table->string('role', 50);
            $table->string('module_key', 100);
            $table->string('action', 50);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['role', 'module_key', 'action']);
            $table->index('role');
        });

        $rows = [];
        $now = now();

        foreach (RoleAccessConfig::roles() as $role => $roleConfig) {
            foreach ($roleConfig['permissions'] as $moduleKey => $moduleConfig) {
                foreach ($moduleConfig['actions'] as $action => $enabled) {
                    $rows[] = [
                        'role' => $role,
                        'module_key' => $moduleKey,
                        'action' => $action,
                        'is_enabled' => (bool) $enabled,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        DB::table('role_permission_settings')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission_settings');
    }
};
