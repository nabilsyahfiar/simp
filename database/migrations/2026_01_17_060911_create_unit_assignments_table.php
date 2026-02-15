<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('house_units')->cascadeOnDelete();
            $table->foreignId('old_foreman_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('new_foreman_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_assignments');
    }
};
