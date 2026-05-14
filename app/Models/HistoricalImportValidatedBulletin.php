<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImportValidatedBulletin extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_id',
        'roster_id',
        'source_bulletin_id',
        'sheet_name',
        'trimester_label',
        'student_name',
        'student_number',
        'class_code',
        'class_label',
        'academic_year_label',
        'source_subject_line_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(HistoricalImportValidatedResult::class, 'validated_bulletin_id');
    }

    public function sourceBulletin(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportBulletin::class, 'source_bulletin_id');
    }
}
