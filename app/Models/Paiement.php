<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'paiement_statut_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference',
        'libelle',
        'observation',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'montant' => 'decimal:2',
    ];

    public function paiementStatut(): BelongsTo
    {
        return $this->belongsTo(PaiementStatut::class);
    }
}
