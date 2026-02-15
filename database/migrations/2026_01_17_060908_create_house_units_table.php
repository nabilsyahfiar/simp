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
        Schema::create('house_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->string('unit_code');
            $table->string('official_status')->default('belum_mulai');
            $table->unsignedTinyInteger('official_progress_percent')->nullable();
            $table->foreignId('assigned_foreman_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'unit_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('house_units');
    }
};
