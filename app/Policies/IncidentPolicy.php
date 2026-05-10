<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;
use App\Support\ArchiveAccess;

class IncidentPolicy
{
    public function view(?User $user, Incident $incident): bool
    {
        return ArchiveAccess::clearance() >= $incident->clearance_level;
    }
}
