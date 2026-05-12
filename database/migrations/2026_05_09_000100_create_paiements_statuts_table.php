<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements_statuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained()->cascadeOnDelete();
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->cascadeOnDelete();
            $table->string('statut', 40)->default('en_retard');
            $table->decimal('montant_attendu', 10, 2)->nullable();
            $table->decimal('montant_paye', 10, 2)->nullable();
            $table->date('date_dernier_paiement')->nullable();
            $table->text('observation')->nullable();
            $table->boolean('autorise_acces_bulletin')->default(false);
            $table->timestamps();

            $table->unique(['eleve_id', 'annee_scolaire_id'], 'paiements_statuts_eleve_annee_unique');
            $table->index(['annee_scolaire_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements_statuts');
    }
};
