<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount(['pages' => fn ($q) => $q->published()])
            ->whereHas('pages', fn ($q) => $q->published())
            ->orderBy('name')
            ->get();

        return view('tags.index', compact('tags'));
    }

    public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $pages = $tag->pages()
            ->published()
            ->with('type')
            ->orderBy('title')
            ->get()
            ->groupBy(fn ($p) => $p->type->name_plural);

        return view('tags.show', compact('tag', 'pages'));
    }
}
