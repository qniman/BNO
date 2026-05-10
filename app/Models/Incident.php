<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'object_entity_id',
        'title',
        'description',
        'severity',
        'casualties',
        'occurred_on',
        'location',
        'clearance_level',
    ];

    protected function casts(): array
    {
        return [
            'occurred_on' => 'date',
        ];
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(ObjectEntity::class, 'object_entity_id');
    }
}
