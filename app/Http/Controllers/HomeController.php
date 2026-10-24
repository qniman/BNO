<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageType;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $types = PageType::where('show_in_nav', true)
            ->orderBy('sort')
            ->withCount(['pages' => fn ($q) => $q->published()])
            ->get();

        $recent = Page::published()
            ->with('type')
            ->latest('published_at')
            ->latest('id')
            ->limit(12)
            ->get();

        return view('home', compact('types', 'recent'));
    }
}
