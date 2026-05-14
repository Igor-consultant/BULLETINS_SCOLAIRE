<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImportBulletin extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_id',
        'panel_id',
        'roster_id',
        'sheet_name',
        'panel_index',
        'anchor_row_index',
        'anchor_cell',
        'trimester_label',
        'student_name',
        'student_number',
        'class_code',
        'class_label',
        'academic_year_label',
        'subject_line_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportBatch::class, 'batch_id');
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportPanel::class, 'panel_id');
    }

    public function roster(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportRoster::class, 'roster_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(HistoricalImportBulletinLine::class, 'bulletin_id');
    }
}
