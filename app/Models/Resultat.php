<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resultat extends Model
{
    use HasFactory;

    protected $fillable = [
        'eleve_id',
        'classe_id',
        'trimestre_id',
        'matiere_id',
        'coefficient',
        'moyenne_devoirs',
        'composition',
        'moyenne_matiere',
        'points',
        'rang',
        'statut_calcul',
    ];

    protected $casts = [
        'coefficient' => 'decimal:2',
        'moyenne_devoirs' => 'decimal:2',
        'composition' => 'decimal:2',
        'moyenne_matiere' => 'decimal:2',
        'points' => 'decimal:2',
        'rang' => 'integer',
    ];

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function trimestre(): BelongsTo
    {
        return $this->belongsTo(Trimestre::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }
}
