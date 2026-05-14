<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalImportRoster extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_id',
        'sheet_name',
        'student_name',
        'candidate_occurrences',
        'panel_presence_count',
        'first_row_index',
        'last_row_index',
        'best_student_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportBatch::class, 'batch_id');
    }

    public function sheet(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportSheet::class, 'sheet_id');
    }
}
