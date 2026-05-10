<?php

namespace App\Services;

use App\Models\ObjectEntity;
use App\Repositories\Contracts\ObjectEntityRepositoryInterface;
use App\Support\ArchiveAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ObjectRegistryService
{
    public function __construct(
        private readonly ObjectEntityRepositoryInterface $objects,
    ) {}

    /**
     * @return LengthAwarePaginator<int, \App\DTO\ObjectListItemDto>
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->objects->paginateVisible(ArchiveAccess::clearance(), $filters);
    }

    public function findForViewer(string $slug): ?ObjectEntity
    {
        return $this->objects->findPublishedBySlug($slug, ArchiveAccess::clearance());
    }
}
