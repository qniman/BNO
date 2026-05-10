<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArchiveAccessLogged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ?int $userId,
        public readonly string $action,
        public readonly ?string $subjectType,
        public readonly ?int $subjectId,
        public readonly array $meta = [],
        public readonly ?string $ip = null,
    ) {}
}
