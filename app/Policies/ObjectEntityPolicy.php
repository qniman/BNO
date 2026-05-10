<?php

namespace App\Policies;

use App\Models\ObjectEntity;
use App\Models\User;
use App\Support\ArchiveAccess;

class ObjectEntityPolicy
{
    public function view(?User $user, ObjectEntity $objectEntity): bool
    {
        if (! $objectEntity->is_active) {
            return false;
        }

        $c = ArchiveAccess::clearance();

        if ($objectEntity->is_hidden && $c < 5) {
            return false;
        }

        return $c >= $objectEntity->clearance_level;
    }

    public function manage(User $user): bool
    {
        return ArchiveAccess::canManageRegistry();
    }
}
