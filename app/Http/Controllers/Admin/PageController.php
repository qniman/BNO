<?php

namespace App\Http\Controllers\Admin;

use App\Content\PageWriter;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(Request $request): View
    {
        $types = PageType::orderBy('sort')->get();
        $type = $types->firstWhere('key', $request->query('type'));

        $pages = Page::with(['type', 'author'])
            ->when($type, fn ($q) => $q->where('page_type_id', $type->id))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('q'), fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$s}%")
                ->orWhere('slug', 'like', "%{$s}%")))
            ->latest('updated_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.pages.index', compact('types', 'type', 'pages'));
    }

    public function create(PageType $type): View
    {
        $page = new Page(['status' => 'draft']);
        $page->setRelation('type', $type);
        $page->number = $type->numbered ? $type->nextNumber() : null;

        return view('admin.pages.edit', [
            'page' => $page,
            'type' => $type,
            'linkedPages' => collect(),
        ]);
    }

    public function store(Request $request, PageType $type, PageWriter $writer): RedirectResponse
    {
        $page = $writer->save(new Page, $type, $this->validated($request, $type), $request->user(), 'Создание');

        return $this->redirectAfterSave($request, $page, 'Страница создана.');
    }

    public function edit(Page $page): View
    {
        $page->load(['type', 'tags']);

        return view('admin.pages.edit', [
            'page' => $page,
            'type' => $page->type,
            'linkedPages' => $this->fieldPages($page),
        ]);
    }

    public function update(Request $request, Page $page, PageWriter $writer): RedirectResponse
    {
        $data = $this->validated($request, $page->type, $page);
        $writer->save($page, $page->type, $data, $request->user(), $data['note'] ?? null);

        return $this->redirectAfterSave($request, $page, 'Сохранено.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index', ['type' => $page->type->key])
            ->with('status', "«{$page->title}» удалена.");
    }

    private function validated(Request $request, PageType $type, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'number' => [
                'nullable', 'integer', 'min:0',
                Rule::unique('pages', 'number')
                    ->where('page_type_id', $type->id)
                    ->whereNull('deleted_at')
                    ->ignore($page?->id),
            ],
            'summary' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(Page::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'fields' => ['nullable', 'array'],
            'content' => ['nullable', 'json'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'slug.regex' => 'Адрес: только латиница в нижнем регистре, цифры и дефис.',
            'number.unique' => 'Такой номер уже занят.',
        ]);

        foreach ($type->fieldDefinitions()->where('required', true) as $field) {
            $value = $data['fields'][$field['key']] ?? null;
            if ($value === null || $value === '' || $value === []) {
                throw ValidationException::withMessages([
                    'fields.'.$field['key'] => "Поле «{$field['label']}» обязательно.",
                ]);
            }
        }

        $content = isset($data['content']) ? json_decode($data['content'], true) : null;
        $data['content'] = is_array($content) && ($content['type'] ?? null) === 'doc' ? $content : null;

        return $data;
    }

    private function redirectAfterSave(Request $request, Page $page, string $message): RedirectResponse
    {
        if ($request->input('after') === 'view') {
            return redirect()->to($page->url());
        }

        return redirect()->route('admin.pages.edit', $page)->with('status', $message);
    }

    /** Страницы, выбранные в полях-ссылках, — чтобы показать их названия в форме. */
    private function fieldPages(Page $page)
    {
        $ids = $page->type->fieldDefinitions()
            ->whereIn('type', ['page', 'pages'])
            ->flatMap(fn ($f) => (array) ($page->fields[$f['key']] ?? []))
            ->all();

        return Page::with('type')->whereIn('id', $ids)->get()->keyBy('id');
    }
}
