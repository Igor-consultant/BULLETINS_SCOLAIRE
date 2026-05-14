<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historical_import_finalizations', function (Blueprint $table) {
            $table->index('batch_id', 'hist_imp_final_batch_idx');
            $table->dropUnique('hist_imp_final_unique');
            $table->unique(
                ['batch_id', 'sheet_name', 'academic_year_label', 'class_code'],
                'hist_imp_final_group_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('historical_import_finalizations', function (Blueprint $table) {
            $table->dropUnique('hist_imp_final_group_unique');
            $table->dropIndex('hist_imp_final_batch_idx');
            $table->unique(['batch_id', 'sheet_name'], 'hist_imp_final_unique');
        });
    }
};
