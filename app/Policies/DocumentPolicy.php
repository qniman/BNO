<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Support\ArchiveAccess;

class DocumentPolicy
{
    public function view(?User $user, Document $document): bool
    {
        return ArchiveAccess::clearance() >= $document->clearance_level;
    }

    public function update(User $user, Document $document): bool
    {
        return ArchiveAccess::canManageRegistry();
    }
}
