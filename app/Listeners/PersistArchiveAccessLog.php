<?php

namespace App\Listeners;

use App\Events\ArchiveAccessLogged;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class PersistArchiveAccessLog implements ShouldQueue
{
    public function handle(ArchiveAccessLogged $event): void
    {
        AuditLog::query()->create([
            'user_id' => $event->userId,
            'action' => $event->action,
            'subject_type' => $event->subjectType,
            'subject_id' => $event->subjectId,
            'meta' => $event->meta,
            'ip_address' => $event->ip,
        ]);
    }
}
