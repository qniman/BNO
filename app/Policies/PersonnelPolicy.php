<?php

namespace App\Policies;

use App\Models\Personnel;
use App\Models\User;
use App\Support\ArchiveAccess;

class PersonnelPolicy
{
    public function view(?User $user, Personnel $personnel): bool
    {
        return ArchiveAccess::clearance() >= $personnel->clearance_level;
    }
}
