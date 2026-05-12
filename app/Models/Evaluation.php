<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'classe_matiere_id',
        'trimestre_id',
        'libelle',
        'type',
        'date_evaluation',
        'note_sur',
        'coefficient_local',
        'statut',
    ];

    protected $casts = [
        'date_evaluation' => 'date',
        'note_sur' => 'decimal:2',
        'coefficient_local' => 'decimal:2',
    ];

    public function classeMatiere(): BelongsTo
    {
        return $this->belongsTo(ClasseMatiere::class);
    }

    public function trimestre(): BelongsTo
    {
        return $this->belongsTo(Trimestre::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
