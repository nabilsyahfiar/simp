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
        Schema::create('status_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('house_units')->cascadeOnDelete();
            $table->string('old_status');
            $table->string('new_status');
            $table->unsignedTinyInteger('old_percent')->nullable();
            $table->unsignedTinyInteger('new_percent')->nullable();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('changed_at');
            $table->text('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_audit_logs');
    }
};
