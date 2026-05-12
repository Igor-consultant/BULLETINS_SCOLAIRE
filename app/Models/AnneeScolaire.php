<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnneeScolaire extends Model
{
    use HasFactory;

    protected $table = 'annees_scolaires';

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function trimestres(): HasMany
    {
        return $this->hasMany(Trimestre::class, 'annee_scolaire_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(Classe::class, 'annee_scolaire_id');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class, 'annee_scolaire_id');
    }

    public function paiementsStatuts(): HasMany
    {
        return $this->hasMany(PaiementStatut::class, 'annee_scolaire_id');
    }
}
