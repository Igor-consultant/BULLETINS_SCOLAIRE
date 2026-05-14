<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('historical_import_finalizations')) {
            Schema::create('historical_import_finalizations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->string('sheet_name');
                $table->string('class_code')->nullable();
                $table->string('academic_year_label')->nullable();
                $table->unsignedBigInteger('annee_scolaire_id')->nullable();
                $table->unsignedBigInteger('classe_id')->nullable();
                $table->unsignedInteger('imported_student_count')->default(0);
                $table->unsignedInteger('imported_bulletin_count')->default(0);
                $table->unsignedInteger('imported_result_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['batch_id', 'sheet_name'], 'hist_imp_final_unique');
                $table->foreign('batch_id', 'hist_imp_final_batch_fk')->references('id')->on('historical_import_batches')->cascadeOnDelete();
                $table->foreign('annee_scolaire_id', 'hist_imp_final_annee_fk')->references('id')->on('annees_scolaires')->nullOnDelete();
                $table->foreign('classe_id', 'hist_imp_final_classe_fk')->references('id')->on('classes')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('historical_import_result_mappings')) {
            Schema::create('historical_import_result_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('finalization_id');
                $table->unsignedBigInteger('validated_bulletin_id');
                $table->unsignedBigInteger('validated_result_id');
                $table->unsignedBigInteger('eleve_id');
                $table->unsignedBigInteger('inscription_id');
                $table->unsignedBigInteger('annee_scolaire_id');
                $table->unsignedBigInteger('trimestre_id');
                $table->unsignedBigInteger('classe_id');
                $table->unsignedBigInteger('matiere_id');
                $table->unsignedBigInteger('resultat_id');
                $table->timestamps();

                $table->unique(['validated_result_id'], 'hist_imp_result_map_val_res_unique');
                $table->foreign('finalization_id', 'hist_imp_result_map_final_fk')->references('id')->on('historical_import_finalizations')->cascadeOnDelete();
                $table->foreign('validated_bulletin_id', 'hist_imp_result_map_val_bul_fk')->references('id')->on('historical_import_validated_bulletins')->cascadeOnDelete();
                $table->foreign('validated_result_id', 'hist_imp_result_map_val_res_fk')->references('id')->on('historical_import_validated_results')->cascadeOnDelete();
                $table->foreign('eleve_id', 'hist_imp_result_map_eleve_fk')->references('id')->on('eleves')->cascadeOnDelete();
                $table->foreign('inscription_id', 'hist_imp_result_map_insc_fk')->references('id')->on('inscriptions')->cascadeOnDelete();
                $table->foreign('annee_scolaire_id', 'hist_imp_result_map_annee_fk')->references('id')->on('annees_scolaires')->cascadeOnDelete();
                $table->foreign('trimestre_id', 'hist_imp_result_map_trim_fk')->references('id')->on('trimestres')->cascadeOnDelete();
                $table->foreign('classe_id', 'hist_imp_result_map_classe_fk')->references('id')->on('classes')->cascadeOnDelete();
                $table->foreign('matiere_id', 'hist_imp_result_map_matiere_fk')->references('id')->on('matieres')->cascadeOnDelete();
                $table->foreign('resultat_id', 'hist_imp_result_map_res_fk')->references('id')->on('resultats')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_import_result_mappings');
        Schema::dropIfExists('historical_import_finalizations');
    }
};
