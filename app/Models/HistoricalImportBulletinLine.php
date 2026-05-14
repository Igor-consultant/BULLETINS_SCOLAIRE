<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricalImportBulletinLine extends Model
{
    protected $fillable = [
        'batch_id',
        'bulletin_id',
        'sheet_name',
        'panel_index',
        'line_row_index',
        'subject_label',
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

    public function bulletin(): BelongsTo
    {
        return $this->belongsTo(HistoricalImportBulletin::class, 'bulletin_id');
    }
}
