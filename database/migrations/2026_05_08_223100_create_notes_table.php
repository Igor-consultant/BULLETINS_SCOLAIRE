<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('eleve_id')->constrained()->cascadeOnDelete();
            $table->decimal('note', 5, 2)->nullable();
            $table->boolean('absence')->default(false);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->unique(['evaluation_id', 'eleve_id']);
            $table->index(['eleve_id', 'absence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
