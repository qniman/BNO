<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObjectEntity extends Model
{
    protected $table = 'object_entities';

    protected $fillable = [
        'number',
        'slug',
        'title',
        'status',
        'threat_class',
        'anomaly_type',
        'containment_level',
        'stability_index',
        'psi_index',
        'amnestic_index',
        'interaction_index',
        'description',
        'containment_protocol',
        'history',
        'is_active',
        'is_hidden',
        'public_archive',
        'clearance_level',
        'dynamic_state',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_hidden' => 'boolean',
            'public_archive' => 'boolean',
            'dynamic_state' => 'array',
        ];
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'object_entity_id');
    }

    public function observationLogs(): HasMany
    {
        return $this->hasMany(ObservationLog::class, 'object_entity_id');
    }

    public function appendices(): HasMany
    {
        return $this->hasMany(Appendix::class, 'object_entity_id')->orderBy('sort_order');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ObjectAttachment::class, 'object_entity_id')->orderBy('sort_order');
    }

    /**
     * Связанные карточки (исходящие ссылки архива).
     *
     * @return BelongsToMany<ObjectEntity, $this>
     */
    public function linkedObjects(): BelongsToMany
    {
        return $this->belongsToMany(
            ObjectEntity::class,
            'object_entity_links',
            'object_entity_id',
            'related_object_entity_id'
        )->withPivot(['relation_type', 'clearance_level']);
    }
}
