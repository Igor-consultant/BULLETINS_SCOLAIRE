<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImportBatch extends Model
{
    protected $fillable = [
        'label',
        'source_path',
        'source_filename',
        'source_hash',
        'status',
        'sheet_count',
        'row_count',
        'cell_count',
        'formula_count',
        'metadata',
        'imported_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'imported_at' => 'datetime',
    ];

    public function sheets(): HasMany
    {
        return $this->hasMany(HistoricalImportSheet::class, 'batch_id');
    }
}
