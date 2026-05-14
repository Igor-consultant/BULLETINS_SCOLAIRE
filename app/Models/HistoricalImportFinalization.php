<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImportFinalization extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_name',
        'class_code',
        'academic_year_label',
        'annee_scolaire_id',
        'classe_id',
        'imported_student_count',
        'imported_bulletin_count',
        'imported_result_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportBatch::class, 'batch_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(HistoricalImportResultMapping::class, 'finalization_id');
    }
}
