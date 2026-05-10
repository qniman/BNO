<?php

namespace App\Repositories\Contracts;

use App\DTO\ObjectListItemDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ObjectEntityRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, ObjectListItemDto>
     */
    public function paginateVisible(int $clearance, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug, int $clearance): ?\App\Models\ObjectEntity;
}
