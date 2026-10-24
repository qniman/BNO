<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class PageType extends Model
{
    /** Шаблоны вывода: ключ → название. Вид страницы задаётся шаблоном, поля — типом. */
    public const LAYOUTS = [
        'object' => 'Объект (карточка с классом и номером)',
        'document' => 'Документ (бланк с реквизитами)',
        'article' => 'Статья (читаемый текст)',
        'info' => 'Информационная страница',
    ];

    public const FIELD_TYPES = [
        'text' => 'Строка',
        'textarea' => 'Многострочный текст',
        'number' => 'Число',
        'date' => 'Дата',
        'select' => 'Выбор из списка',
        'boolean' => 'Да / нет',
        'page' => 'Ссылка на страницу',
        'pages' => 'Ссылки на страницы',
    ];

    /** Префиксы, занятые системными маршрутами. */
    public const RESERVED_PREFIXES = ['admin', 'login', 'logout', 'tag', 'tags', 'search', 'storage', 'build', 'up', 'media'];

    protected $fillable = [
        'key', 'name', 'name_plural', 'url_prefix', 'layout', 'description',
        'numbered', 'number_format', 'fields', 'show_in_nav', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'numbered' => 'boolean',
            'show_in_nav' => 'boolean',
        ];
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * @return Collection<int, array{key: string, label: string, type: string, options?: array, page_type?: ?string, required?: bool, header?: bool, list?: bool, help?: ?string}>
     */
    public function fieldDefinitions(): Collection
    {
        return collect($this->fields ?? [])->filter(fn ($f) => ! empty($f['key']))->values();
    }

    public function field(string $key): ?array
    {
        return $this->fieldDefinitions()->firstWhere('key', $key);
    }

    /** «БНО-{n:3}» + 7 → «БНО-007». */
    public function formatNumber(?int $number): ?string
    {
        if (! $this->numbered || $number === null) {
            return null;
        }

        $format = $this->number_format ?: '{n}';

        return preg_replace_callback('/\{n(?::(\d+))?\}/u', function ($m) use ($number) {
            $pad = isset($m[1]) ? (int) $m[1] : 0;

            return str_pad((string) $number, $pad, '0', STR_PAD_LEFT);
        }, $format);
    }

    public function nextNumber(): int
    {
        return (int) $this->pages()->withTrashed()->max('number') + 1;
    }

    public function url(): string
    {
        return route('pages.index', $this->url_prefix);
    }
}
