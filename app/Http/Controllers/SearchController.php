<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $pages = collect();

        if (mb_strlen($q) >= 2) {
            $like = '%'.str_replace(['%', '_'], ' ', $q).'%';

            $pages = Page::published()
                ->with('type')
                ->where(fn ($w) => $w->where('title', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('content_text', 'like', $like)
                    ->orWhere('slug', 'like', '%'.Str::slug($q).'%'))
                ->orderByRaw('case when title like ? then 0 else 1 end', [$like])
                ->limit(100)
                ->get();
        }

        return view('search', [
            'q' => $q,
            'pages' => $pages,
            'snippet' => fn (Page $p) => $this->snippet($p, $q),
        ]);
    }

    private function snippet(Page $page, string $q): string
    {
        $text = preg_replace('/\s+/u', ' ', (string) $page->content_text);
        $pos = mb_stripos($text, $q);

        if ($pos === false) {
            return $page->summary ?: Str::limit($text, 200);
        }

        $start = max(0, $pos - 80);

        return ($start > 0 ? '…' : '').mb_substr($text, $start, 220).'…';
    }
}
