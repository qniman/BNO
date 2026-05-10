<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRevision extends Model
{
    protected $fillable = [
        'document_id',
        'revision_label',
        'revised_at',
        'editor_id',
        'change_reason',
        'content_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'revised_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }
}
