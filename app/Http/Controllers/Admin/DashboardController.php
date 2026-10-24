<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageType;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $types = PageType::orderBy('sort')
            ->withCount([
                'pages',
                'pages as drafts_count' => fn ($q) => $q->where('status', 'draft'),
            ])
            ->get();

        $recent = Page::with(['type', 'author'])->latest('updated_at')->limit(15)->get();

        return view('admin.dashboard', compact('types', 'recent'));
    }
}
