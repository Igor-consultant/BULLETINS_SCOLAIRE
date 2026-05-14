<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalImportValidatedResult extends Model
{
    protected $fillable = [
        'batch_id',
        'validated_bulletin_id',
        'source_line_id',
        'sheet_name',
        'trimester_label',
        'student_name',
        'student_number',
        'subject_label_original',
        'subject_label_normalized',
        'note_classe',
        'composition',
        'moyenne_sur_20',
        'coefficient',
        'points',
        'rang',
        'teacher_name',
        'appreciation',
        'metadata',
    ];

    protected $casts = [
        'note_classe' => 'decimal:4',
        'composition' => 'decimal:4',
        'moyenne_sur_20' => 'decimal:4',
        'coefficient' => 'decimal:4',
        'points' => 'decimal:4',
        'metadata' => 'array',
    ];

    public function validatedBulletin(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportValidatedBulletin::class, 'validated_bulletin_id');
    }

    public function sourceLine(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportBulletinLine::class, 'source_line_id');
    }
}
