<?php

namespace App\Http\Controllers;

use App\Content\ContentRenderer;
use App\Content\FieldPresenter;
use App\Models\Page;
use App\Models\PageType;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(Request $request, string $prefix): View
    {
        $type = PageType::where('url_prefix', $prefix)->firstOrFail();

        $query = $type->pages()->published()->with(['type', 'tags']);

        $tag = $request->filled('tag') ? Tag::where('slug', $request->query('tag'))->first() : null;
        if ($tag) {
            $query->whereHas('tags', fn ($q) => $q->whereKey($tag->id));
        }

        // Фильтры по полям-спискам: ?f[class]=Кетер
        $filters = [];
        foreach ($type->fieldDefinitions()->where('type', 'select') as $field) {
            $value = $request->input('f.'.$field['key']);
            if (in_array($value, $field['options'] ?? [], true)) {
                $filters[$field['key']] = $value;
                $query->where('fields->'.$field['key'], $value);
            }
        }

        $sort = $request->query('sort', $type->numbered ? 'number' : 'title');
        match ($sort) {
            'recent' => $query->latest('published_at')->latest('id'),
            'title' => $query->orderBy('title'),
            default => $query->orderBy('number')->orderBy('title'),
        };

        $pages = $query->paginate(40)->withQueryString();

        return view('pages.index', [
            'type' => $type,
            'pages' => $pages,
            'tag' => $tag,
            'filters' => $filters,
            'sort' => $sort,
            'presenter' => new FieldPresenter($pages),
        ]);
    }

    public function show(string $prefix, string $slug, ContentRenderer $renderer): View
    {
        $type = PageType::where('url_prefix', $prefix)->firstOrFail();

        /** @var Page $page */
        $page = $type->pages()->where('slug', $slug)->with(['type', 'tags', 'author'])->firstOrFail();

        abort_unless($page->isPublished() || auth()->check(), 404);

        $backlinks = $page->backlinks()
            ->with('type')
            ->when(! auth()->check(), fn ($q) => $q->published())
            ->get()
            ->unique('id')
            ->groupBy(fn (Page $p) => $p->type->name_plural);

        return view('pages.show', [
            'type' => $type,
            'page' => $page,
            'body' => $renderer->render($page->content),
            'presenter' => new FieldPresenter([$page]),
            'backlinks' => $backlinks,
        ]);
    }
}
