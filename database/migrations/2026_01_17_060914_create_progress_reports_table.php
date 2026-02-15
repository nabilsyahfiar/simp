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
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('house_units')->cascadeOnDelete();
            $table->foreignId('foreman_id')->constrained('users')->restrictOnDelete();
            $table->text('description');
            $table->unsignedTinyInteger('reported_percent');
            $table->string('status')->default('pending');
            $table->timestamp('report_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};
