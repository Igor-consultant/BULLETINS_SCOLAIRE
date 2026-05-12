<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Eleve extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricule',
        'nom',
        'prenoms',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'contact_principal',
        'nom_parent',
        'contact_parent',
        'adresse',
        'actif',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'actif' => 'boolean',
    ];

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function resultats(): HasMany
    {
        return $this->hasMany(Resultat::class);
    }

    public function paiementsStatuts(): HasMany
    {
        return $this->hasMany(PaiementStatut::class);
    }

    public function parentEleves(): HasMany
    {
        return $this->hasMany(ParentEleve::class);
    }
}
