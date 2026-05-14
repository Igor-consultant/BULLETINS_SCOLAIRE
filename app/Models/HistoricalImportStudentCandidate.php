<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalImportStudentCandidate extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_id',
        'panel_id',
        'sheet_name',
        'excel_row_index',
        'panel_index',
        'source_name_cell',
        'source_number_cell',
        'student_number',
        'student_name',
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

    public function panel(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportPanel::class, 'panel_id');
    }
}
