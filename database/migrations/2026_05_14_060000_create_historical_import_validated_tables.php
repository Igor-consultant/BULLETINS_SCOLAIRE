<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('historical_import_validated_bulletins')) {
            Schema::create('historical_import_validated_bulletins', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedBigInteger('sheet_id');
                $table->unsignedBigInteger('roster_id')->nullable();
                $table->unsignedBigInteger('source_bulletin_id');
                $table->string('sheet_name');
                $table->string('trimester_label')->nullable();
                $table->string('student_name');
                $table->unsignedInteger('student_number')->nullable();
                $table->string('class_code')->nullable();
                $table->string('class_label')->nullable();
                $table->string('academic_year_label')->nullable();
                $table->unsignedInteger('source_subject_line_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['batch_id', 'sheet_id', 'student_name', 'trimester_label'], 'hist_imp_valid_bul_unique');
                $table->foreign('batch_id', 'hist_val_bul_batch_fk')->references('id')->on('historical_import_batches')->cascadeOnDelete();
                $table->foreign('sheet_id', 'hist_val_bul_sheet_fk')->references('id')->on('historical_import_sheets')->cascadeOnDelete();
                $table->foreign('roster_id', 'hist_val_bul_roster_fk')->references('id')->on('historical_import_rosters')->nullOnDelete();
                $table->foreign('source_bulletin_id', 'hist_val_bul_source_fk')->references('id')->on('historical_import_bulletins')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('historical_import_validated_results')) {
            Schema::create('historical_import_validated_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedBigInteger('validated_bulletin_id');
                $table->unsignedBigInteger('source_line_id');
                $table->string('sheet_name');
                $table->string('trimester_label')->nullable();
                $table->string('student_name');
                $table->unsignedInteger('student_number')->nullable();
                $table->string('subject_label_original');
                $table->string('subject_label_normalized');
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

                $table->unique(['validated_bulletin_id', 'subject_label_normalized'], 'hist_imp_valid_res_unique');
                $table->index(['batch_id', 'sheet_name', 'trimester_label'], 'hist_imp_valid_res_lookup_idx');
                $table->foreign('batch_id', 'hist_val_res_batch_fk')->references('id')->on('historical_import_batches')->cascadeOnDelete();
                $table->foreign('validated_bulletin_id', 'hist_val_res_bulletin_fk')->references('id')->on('historical_import_validated_bulletins')->cascadeOnDelete();
                $table->foreign('source_line_id', 'hist_val_res_line_fk')->references('id')->on('historical_import_bulletin_lines')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_import_validated_results');
        Schema::dropIfExists('historical_import_validated_bulletins');
    }
};
