<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('house_units')
            ->whereNull('official_progress_percent')
            ->update(['official_progress_percent' => 0]);

        DB::table('house_units')
            ->whereNull('official_status')
            ->update(['official_status' => 'belum_mulai']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
