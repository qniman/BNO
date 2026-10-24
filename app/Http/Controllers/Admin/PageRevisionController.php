<?php

namespace App\Http\Controllers\Admin;

use App\Content\ContentRenderer;
use App\Content\PageWriter;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageRevisionController extends Controller
{
    public function index(Page $page): View
    {
        $revisions = $page->revisions()->with('user')->paginate(50);

        return view('admin.revisions.index', compact('page', 'revisions'));
    }

    public function show(Page $page, PageRevision $revision, ContentRenderer $renderer): View
    {
        abort_unless($revision->page_id === $page->id, 404);

        return view('admin.revisions.show', [
            'page' => $page,
            'revision' => $revision,
            'body' => $renderer->render($revision->content),
        ]);
    }

    public function restore(Request $request, Page $page, PageRevision $revision, PageWriter $writer): RedirectResponse
    {
        abort_unless($revision->page_id === $page->id, 404);

        $writer->save($page, $page->type, [
            'title' => $revision->title,
            'slug' => $page->slug,
            'number' => $page->number,
            'summary' => $revision->summary,
            'status' => $page->status,
            'fields' => $revision->fields ?? [],
            'content' => $revision->content,
            'tags' => implode(', ', $revision->tags ?? []),
        ], $request->user(), 'Восстановлена правка от '.$revision->created_at->format('d.m.Y H:i'));

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Правка восстановлена.');
    }
}
