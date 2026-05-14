<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImportSheet extends Model
{
    protected $fillable = [
        'batch_id',
        'sheet_name',
        'worksheet_path',
        'dimension_ref',
        'row_count',
        'non_empty_cell_count',
        'formula_cell_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportBatch::class, 'batch_id');
    }

    public function cells(): HasMany
    {
        return $this->hasMany(HistoricalImportCell::class, 'sheet_id');
    }
}
