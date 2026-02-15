<?php

use App\Support\RoleMenuAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_menu_settings', function (Blueprint $table) {
            $table->id();
            $table->string('role', 50);
            $table->string('menu_key', 100);
            $table->string('menu_label', 100);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['role', 'menu_key']);
            $table->index('role');
        });

        $now = now();
        $rows = [];

        foreach (RoleMenuAccess::defaults() as $role => $menus) {
            foreach ($menus as $menuKey => $menuLabel) {
                $rows[] = [
                    'role' => $role,
                    'menu_key' => $menuKey,
                    'menu_label' => $menuLabel,
                    'is_enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('role_menu_settings')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_menu_settings');
    }
};
