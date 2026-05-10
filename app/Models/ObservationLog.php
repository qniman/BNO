<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationLog extends Model
{
    protected $fillable = [
        'object_entity_id',
        'logged_at',
        'entry',
        'clearance_level',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(ObjectEntity::class, 'object_entity_id');
    }
}
