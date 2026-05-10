<?php

namespace App\DTO;

use App\Models\ObjectEntity;

readonly class ObjectListItemDto
{
    public function __construct(
        public string $number,
        public string $slug,
        public string $title,
        public string $status,
        public ?string $threatClass,
        public int $clearanceLevel,
    ) {}

    public static function fromModel(ObjectEntity $o): self
    {
        return new self(
            number: $o->number,
            slug: $o->slug,
            title: $o->title,
            status: $o->status,
            threatClass: $o->threat_class,
            clearanceLevel: $o->clearance_level,
        );
    }
}
