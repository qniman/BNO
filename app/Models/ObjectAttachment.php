<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ObjectAttachment extends Model
{
    protected $fillable = [
        'object_entity_id',
        'label',
        'kind',
        'path',
        'clearance_level',
        'sort_order',
    ];

    public function object(): BelongsTo
    {
        return $this->belongsTo(ObjectEntity::class, 'object_entity_id');
    }

    public function publicUrl(): string
    {
        $path = $this->path;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
