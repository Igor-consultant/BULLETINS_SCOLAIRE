<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eleve_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trimestre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matiere_id')->constrained()->cascadeOnDelete();
            $table->decimal('coefficient', 5, 2);
            $table->decimal('moyenne_devoirs', 5, 2)->nullable();
            $table->decimal('composition', 5, 2)->nullable();
            $table->decimal('moyenne_matiere', 5, 2)->nullable();
            $table->decimal('points', 8, 2)->nullable();
            $table->unsignedInteger('rang')->nullable();
            $table->string('statut_calcul', 20)->default('provisoire');
            $table->timestamps();

            $table->unique(['eleve_id', 'classe_id', 'trimestre_id', 'matiere_id'], 'resultats_unique_ligne');
            $table->index(['classe_id', 'trimestre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultats');
    }
};
