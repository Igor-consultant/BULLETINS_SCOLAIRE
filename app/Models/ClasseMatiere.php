<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClasseMatiere extends Model
{
    use HasFactory;

    protected $table = 'classe_matieres';

    protected $fillable = [
        'classe_id',
        'matiere_id',
        'coefficient',
        'enseignant_nom',
        'actif',
    ];

    protected $casts = [
        'coefficient' => 'decimal:2',
        'actif' => 'boolean',
    ];

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }
}
