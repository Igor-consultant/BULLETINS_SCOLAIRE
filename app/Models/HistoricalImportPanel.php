<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImportPanel extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_id',
        'sheet_name',
        'panel_index',
        'header_row_index',
        'start_column_index',
        'end_column_index',
        'start_column_letters',
        'end_column_letters',
        'name_header_cell',
        'student_name_column_index',
        'student_number_column_index',
        'detected_student_count',
        'detected_bulletin_count',
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

    public function studentCandidates(): HasMany
    {
        return $this->hasMany(HistoricalImportStudentCandidate::class, 'panel_id');
    }
}
