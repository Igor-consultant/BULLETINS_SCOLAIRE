<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalImportCell extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_id',
        'sheet_name',
        'row_index',
        'column_index',
        'cell_reference',
        'cell_type',
        'raw_value',
        'display_value',
        'formula',
        'is_formula',
    ];

    protected $casts = [
        'is_formula' => 'boolean',
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
