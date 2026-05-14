<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_import_bulletins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('historical_import_batches')->cascadeOnDelete();
            $table->foreignId('sheet_id')->constrained('historical_import_sheets')->cascadeOnDelete();
            $table->foreignId('panel_id')->constrained('historical_import_panels')->cascadeOnDelete();
            $table->foreignId('roster_id')->nullable()->constrained('historical_import_rosters')->nullOnDelete();
            $table->string('sheet_name');
            $table->unsignedInteger('panel_index');
            $table->unsignedInteger('anchor_row_index');
            $table->string('anchor_cell', 16)->nullable();
            $table->string('trimester_label')->nullable();
            $table->string('student_name');
            $table->unsignedInteger('student_number')->nullable();
            $table->string('class_code')->nullable();
            $table->string('class_label')->nullable();
            $table->string('academic_year_label')->nullable();
            $table->unsignedInteger('subject_line_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'sheet_id', 'panel_id']);
        });

        Schema::create('historical_import_bulletin_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('historical_import_batches')->cascadeOnDelete();
            $table->foreignId('bulletin_id')->constrained('historical_import_bulletins')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->unsignedInteger('panel_index');
            $table->unsignedInteger('line_row_index');
            $table->string('subject_label');
            $table->decimal('note_classe', 10, 4)->nullable();
            $table->decimal('composition', 10, 4)->nullable();
            $table->decimal('moyenne_sur_20', 10, 4)->nullable();
            $table->decimal('coefficient', 10, 4)->nullable();
            $table->decimal('points', 12, 4)->nullable();
            $table->unsignedInteger('rang')->nullable();
            $table->string('teacher_name')->nullable();
            $table->string('appreciation')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'sheet_name', 'subject_label'], 'hist_imp_bulletin_lines_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_import_bulletin_lines');
        Schema::dropIfExists('historical_import_bulletins');
    }
};
