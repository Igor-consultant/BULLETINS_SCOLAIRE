<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaiementStatut extends Model
{
    use HasFactory;

    protected $table = 'paiements_statuts';

    protected $fillable = [
        'eleve_id',
        'annee_scolaire_id',
        'statut',
        'montant_attendu',
        'montant_paye',
        'date_dernier_paiement',
        'observation',
        'autorise_acces_bulletin',
    ];

    protected $casts = [
        'montant_attendu' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_dernier_paiement' => 'date',
        'autorise_acces_bulletin' => 'boolean',
    ];

    public function eleve(): BelongsTo
    {
        return $this->belongsTo(Eleve::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class, 'annee_scolaire_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }
}
