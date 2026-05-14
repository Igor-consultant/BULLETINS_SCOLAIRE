<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('historical_import_panels')) {
            Schema::create('historical_import_panels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('historical_import_batches')->cascadeOnDelete();
                $table->foreignId('sheet_id')->constrained('historical_import_sheets')->cascadeOnDelete();
                $table->string('sheet_name');
                $table->unsignedInteger('panel_index');
                $table->unsignedInteger('header_row_index');
                $table->unsignedInteger('start_column_index');
                $table->unsignedInteger('end_column_index');
                $table->string('start_column_letters', 10);
                $table->string('end_column_letters', 10);
                $table->string('name_header_cell', 16);
                $table->unsignedInteger('student_name_column_index');
                $table->unsignedInteger('student_number_column_index')->nullable();
                $table->unsignedInteger('detected_student_count')->default(0);
                $table->unsignedInteger('detected_bulletin_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['batch_id', 'sheet_id', 'panel_index'], 'historical_import_panels_unique_panel');
            });
        }

        if (! Schema::hasTable('historical_import_student_candidates')) {
            Schema::create('historical_import_student_candidates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('historical_import_batches')->cascadeOnDelete();
                $table->foreignId('sheet_id')->constrained('historical_import_sheets')->cascadeOnDelete();
                $table->foreignId('panel_id')->constrained('historical_import_panels')->cascadeOnDelete();
                $table->string('sheet_name');
                $table->unsignedInteger('excel_row_index');
                $table->unsignedInteger('panel_index');
                $table->string('source_name_cell', 16);
                $table->string('source_number_cell', 16)->nullable();
                $table->unsignedInteger('student_number')->nullable();
                $table->string('student_name');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['panel_id', 'excel_row_index'], 'historical_import_students_unique_row');
                $table->index(['batch_id', 'sheet_id', 'student_name'], 'hist_imp_students_lookup_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_import_student_candidates');
        Schema::dropIfExists('historical_import_panels');
    }
};
