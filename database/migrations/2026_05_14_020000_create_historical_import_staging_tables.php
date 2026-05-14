<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();
            $table->string('source_path');
            $table->string('source_filename');
            $table->string('source_hash', 64)->index();
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('sheet_count')->default(0);
            $table->unsignedBigInteger('row_count')->default(0);
            $table->unsignedBigInteger('cell_count')->default(0);
            $table->unsignedBigInteger('formula_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('historical_import_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('historical_import_batches')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->string('worksheet_path');
            $table->string('dimension_ref')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedBigInteger('non_empty_cell_count')->default(0);
            $table->unsignedBigInteger('formula_cell_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'sheet_name']);
        });

        Schema::create('historical_import_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('historical_import_batches')->cascadeOnDelete();
            $table->foreignId('sheet_id')->constrained('historical_import_sheets')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->unsignedInteger('row_index');
            $table->unsignedInteger('column_index');
            $table->string('cell_reference', 16);
            $table->string('cell_type', 30);
            $table->text('raw_value')->nullable();
            $table->text('display_value')->nullable();
            $table->text('formula')->nullable();
            $table->boolean('is_formula')->default(false);
            $table->timestamps();

            $table->index(['sheet_id', 'row_index']);
            $table->index(['sheet_name', 'cell_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_import_cells');
        Schema::dropIfExists('historical_import_sheets');
        Schema::dropIfExists('historical_import_batches');
    }
};
