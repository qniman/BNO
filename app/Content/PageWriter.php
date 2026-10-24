<?php

namespace App\Content;

use App\Models\Page;
use App\Models\PageType;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Сохранение страницы: поля типа, теги, связи и запись в историю правок — в одной транзакции.
 */
class PageWriter
{
    /**
     * @param  array{title: string, slug?: ?string, number?: ?int, summary?: ?string, status: string, published_at?: ?string, fields?: array, content?: ?array, tags?: ?string}  $data
     */
    public function save(Page $page, PageType $type, array $data, ?User $user, ?string $note = null): Page
    {
        return DB::transaction(function () use ($page, $type, $data, $user, $note) {
            $content = $data['content'] ?? null;
            $wasPublished = $page->exists && $page->status === 'published';

            $page->fill([
                'page_type_id' => $type->id,
                'title' => trim($data['title']),
                'summary' => $data['summary'] ?? null,
                'status' => $data['status'],
                'fields' => $this->normalizeFields($type, $data['fields'] ?? []),
                'content' => $content,
                'content_text' => trim(ContentAnalyzer::plainText($content)),
            ]);

            if ($type->numbered) {
                $page->number = $data['number'] ?? $page->number ?? $type->nextNumber();
            } else {
                $page->number = null;
            }

            $page->slug = $this->uniqueSlug($type, $data['slug'] ?? null, $page);

            if (! empty($data['published_at'])) {
                $page->published_at = $data['published_at'];
            } elseif ($page->status === 'published' && ! $wasPublished && ! $page->published_at) {
                $page->published_at = now();
            }

            $page->author_id ??= $user?->id;
            $page->save();

            $page->tags()->sync(Tag::idsFromString($data['tags'] ?? ''));
            $this->rebuildLinks($page, $type);
            $this->recordRevision($page, $user, $note);

            return $page;
        });
    }

    public function normalizeFields(PageType $type, array $input): array
    {
        $values = [];

        foreach ($type->fieldDefinitions() as $field) {
            $raw = $input[$field['key']] ?? null;

            $values[$field['key']] = match ($field['type']) {
                'number' => is_numeric($raw) ? $raw + 0 : null,
                'date' => $raw && strtotime($raw) ? date('Y-m-d', strtotime($raw)) : null,
                'boolean' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
                'select' => in_array($raw, $field['options'] ?? [], true) ? $raw : null,
                'page' => is_numeric($raw) ? (int) $raw : null,
                'pages' => collect(is_array($raw) ? $raw : explode(',', (string) $raw))
                    ->filter(fn ($id) => is_numeric($id))->map(fn ($id) => (int) $id)->unique()->values()->all(),
                default => ($s = trim((string) $raw)) !== '' ? $s : null,
            };
        }

        return $values;
    }

    private function uniqueSlug(PageType $type, ?string $wanted, Page $page): string
    {
        $base = Str::slug($wanted ?: ($type->numbered && $page->number ? $type->formatNumber($page->number) : $page->title)) ?: 'page';
        $slug = $base;
        $i = 2;

        while (Page::withTrashed()
            ->where('page_type_id', $type->id)
            ->where('slug', $slug)
            ->when($page->exists, fn ($q) => $q->whereKeyNot($page->id))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function rebuildLinks(Page $page, PageType $type): void
    {
        $rows = collect(ContentAnalyzer::linkedPageIds($page->content))
            ->map(fn ($id) => ['to' => $id, 'source' => 'content']);

        foreach ($type->fieldDefinitions() as $field) {
            if (! in_array($field['type'], ['page', 'pages'], true)) {
                continue;
            }
            foreach ((array) ($page->fields[$field['key']] ?? []) as $id) {
                $rows->push(['to' => (int) $id, 'source' => 'field:'.$field['key']]);
            }
        }

        $existing = Page::withTrashed()->whereIn('id', $rows->pluck('to'))->pluck('id')->all();

        DB::table('page_links')->where('from_page_id', $page->id)->delete();
        DB::table('page_links')->insert(
            $rows->filter(fn ($r) => in_array($r['to'], $existing, true) && $r['to'] !== $page->id)
                ->unique(fn ($r) => $r['to'].'|'.$r['source'])
                ->map(fn ($r) => ['from_page_id' => $page->id, 'to_page_id' => $r['to'], 'source' => $r['source']])
                ->values()->all()
        );
    }

    private function recordRevision(Page $page, ?User $user, ?string $note): void
    {
        $snapshot = [
            'title' => $page->title,
            'summary' => $page->summary,
            'fields' => $page->fields,
            'content' => $page->content,
            'tags' => $page->tags()->pluck('name')->all(),
        ];

        $last = $page->revisions()->first();
        if ($last && $last->only(array_keys($snapshot)) == $snapshot) {
            return;
        }

        $page->revisions()->create($snapshot + ['user_id' => $user?->id, 'note' => $note]);
    }
}
