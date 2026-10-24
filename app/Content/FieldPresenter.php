<?php

namespace App\Content;

use App\Models\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Вывод значений полей типа: даты по-русски, ссылки на страницы — ссылками.
 */
class FieldPresenter
{
    /** @var Collection<int, Page> */
    private Collection $pages;

    /**
     * @param  iterable<Page>  $pages  страницы, для которых будут выводиться поля (для предзагрузки ссылок)
     */
    public function __construct(iterable $pages)
    {
        $ids = [];
        foreach ($pages as $page) {
            foreach ($page->type->fieldDefinitions() as $field) {
                if (in_array($field['type'], ['page', 'pages'], true)) {
                    array_push($ids, ...array_map('intval', (array) ($page->fields[$field['key']] ?? [])));
                }
            }
        }

        $this->pages = Page::with('type')->whereIn('id', array_unique($ids))->get()->keyBy('id');
    }

    public function html(array $field, mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        return match ($field['type']) {
            'boolean' => $value ? 'Да' : 'Нет',
            'date' => e(Carbon::parse($value)->translatedFormat('d.m.Y')),
            'textarea' => nl2br(e($value), false),
            'page', 'pages' => $this->pageLinks((array) $value),
            default => e((string) $value),
        };
    }

    private function pageLinks(array $ids): ?string
    {
        $links = collect($ids)
            ->map(fn ($id) => $this->pages->get((int) $id))
            ->filter(fn (?Page $p) => $p && ($p->isPublished() || auth()->check()))
            ->map(fn (Page $p) => '<a href="'.e($p->url()).'" class="page-link">'.e($p->code() ?? $p->title).'</a>');

        return $links->isEmpty() ? null : $links->implode(', ');
    }
}
