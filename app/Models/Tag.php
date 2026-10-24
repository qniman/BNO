<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class);
    }

    /**
     * «класс-а, меметика» → id тегов (создаёт отсутствующие).
     *
     * @return Collection<int, int>
     */
    public static function idsFromString(?string $input): Collection
    {
        return collect(preg_split('/[,;\n]+/u', (string) $input))
            ->map(fn ($name) => trim(mb_strtolower($name)))
            ->filter()
            ->unique()
            ->map(function (string $name) {
                $slug = Str::slug($name) ?: md5($name);

                return static::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            })
            ->values();
    }

    public function url(): string
    {
        return route('tags.show', $this->slug);
    }
}
