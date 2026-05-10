<?php

namespace App\Repositories;

use App\DTO\ObjectListItemDto;
use App\Models\ObjectEntity;
use App\Repositories\Contracts\ObjectEntityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentObjectEntityRepository implements ObjectEntityRepositoryInterface
{
    public function paginateVisible(int $clearance, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = ObjectEntity::query()
            ->where('is_active', true)
            ->where('clearance_level', '<=', max($clearance, 1))
            ->when($clearance < 5, fn ($q) => $q->where('is_hidden', false));

        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('number', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if (! empty($filters['threat_class'])) {
            $query->where('threat_class', $filters['threat_class']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sort = $filters['sort'] ?? 'number';
        $dir = $filters['dir'] === 'desc' ? 'desc' : 'asc';
        match ($sort) {
            'title' => $query->orderBy('title', $dir),
            'threat' => $query->orderBy('threat_class', $dir)->orderBy('number'),
            default => $query->orderBy('number', $dir),
        };

        /** @var LengthAwarePaginator<int, ObjectEntity> $paginator */
        $paginator = $query->paginate($perPage)->withQueryString();

        return $paginator->through(fn (ObjectEntity $o) => ObjectListItemDto::fromModel($o));
    }

    public function findPublishedBySlug(string $slug, int $clearance): ?ObjectEntity
    {
        $object = ObjectEntity::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $object) {
            return null;
        }

        if ($object->is_hidden && $clearance < 5) {
            return null;
        }

        if ($object->clearance_level > $clearance) {
            return null;
        }

        return $object;
    }
}
