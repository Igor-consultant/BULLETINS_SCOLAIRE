<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_matiere_id')->constrained('classe_matieres')->cascadeOnDelete();
            $table->foreignId('trimestre_id')->constrained()->cascadeOnDelete();
            $table->string('libelle');
            $table->string('type', 20);
            $table->date('date_evaluation')->nullable();
            $table->decimal('note_sur', 5, 2)->default(20);
            $table->decimal('coefficient_local', 5, 2)->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->timestamps();

            $table->index(['classe_matiere_id', 'trimestre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
