<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_import_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('historical_import_batches')->cascadeOnDelete();
            $table->foreignId('sheet_id')->constrained('historical_import_sheets')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->string('student_name');
            $table->unsignedInteger('candidate_occurrences')->default(0);
            $table->unsignedInteger('panel_presence_count')->default(0);
            $table->unsignedInteger('first_row_index')->nullable();
            $table->unsignedInteger('last_row_index')->nullable();
            $table->unsignedInteger('best_student_number')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'sheet_id', 'student_name'], 'hist_imp_roster_unique_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_import_rosters');
    }
};
