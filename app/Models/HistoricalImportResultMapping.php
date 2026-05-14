<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalImportResultMapping extends Model
{
    protected $fillable = [
        'finalization_id',
        'validated_bulletin_id',
        'validated_result_id',
        'eleve_id',
        'inscription_id',
        'annee_scolaire_id',
        'trimestre_id',
        'classe_id',
        'matiere_id',
        'resultat_id',
    ];

    public function finalization(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportFinalization::class, 'finalization_id');
    }
}
