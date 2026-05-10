<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'content',
        'classification',
        'author_id',
        'clearance_level',
        'is_redacted',
        'volatile',
        'published_at',
        'revision_label',
        'stamp',
        'registration_number',
        'routing_note',
        'acknowledgment_stub',
        'marginalia',
        'corruption_level',
        'chronology_anomaly',
        'leaked_publish',
        'references_object_slug',
    ];

    protected function casts(): array
    {
        return [
            'is_redacted' => 'boolean',
            'volatile' => 'boolean',
            'published_at' => 'datetime',
            'chronology_anomaly' => 'boolean',
            'leaked_publish' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocumentRevision::class)->orderByDesc('revised_at');
    }
}
