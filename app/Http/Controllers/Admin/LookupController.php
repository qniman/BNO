<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    /** Поиск страниц для редактора и полей-ссылок. */
    public function pages(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q'));
        $typeKey = $request->query('type');

        $pages = Page::with('type')
            ->when($typeKey, fn ($w) => $w->whereHas('type', fn ($t) => $t->where('key', $typeKey)))
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s
                ->where('title', 'like', "%{$q}%")
                ->orWhere('slug', 'like', "%{$q}%")
                ->orWhere('number', ltrim(preg_replace('/\D/', '', $q), '0') ?: -1)))
            ->orderBy('title')
            ->limit(20)
            ->get();

        return response()->json($pages->map(fn (Page $p) => [
            'id' => $p->id,
            'title' => $p->title,
            'code' => $p->code(),
            'label' => $p->fullTitle(),
            'type' => $p->type->name,
            'url' => $p->url(),
            'status' => $p->status,
        ]));
    }
}
