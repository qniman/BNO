<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'draft' => 'Черновик',
        'published' => 'Опубликовано',
    ];

    protected $fillable = [
        'page_type_id', 'title', 'slug', 'number', 'summary', 'fields', 'content',
        'content_text', 'status', 'published_at', 'author_id',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'content' => 'array',
            'published_at' => 'datetime',
            'number' => 'integer',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PageType::class, 'page_type_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest('id');
    }

    /** Страницы, которые ссылаются на эту. */
    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(Page::class, 'page_links', 'to_page_id', 'from_page_id')
            ->withPivot('source')
            ->distinct();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && ($this->published_at === null || $this->published_at->isPast());
    }

    /** Номер в формате типа, например «БНО-042». */
    public function code(): ?string
    {
        return $this->type?->formatNumber($this->number);
    }

    public function fullTitle(): string
    {
        $code = $this->code();

        return $code ? "{$code} · {$this->title}" : $this->title;
    }

    public function url(): string
    {
        return route('pages.show', [$this->type->url_prefix, $this->slug]);
    }

    public function fieldValue(string $key): mixed
    {
        return $this->fields[$key] ?? null;
    }
}
