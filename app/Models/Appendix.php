<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appendix extends Model
{
    protected $table = 'appendices';

    protected $fillable = [
        'object_entity_id',
        'label',
        'body',
        'clearance_level',
        'sort_order',
        'corrupted',
    ];

    protected function casts(): array
    {
        return [
            'corrupted' => 'boolean',
        ];
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(ObjectEntity::class, 'object_entity_id');
    }
}
