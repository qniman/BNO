<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageTypeController extends Controller
{
    public function index(): View
    {
        $types = PageType::orderBy('sort')->withCount('pages')->get();

        return view('admin.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.types.edit', [
            'type' => new PageType(['layout' => 'article', 'show_in_nav' => true, 'fields' => []]),
            'allTypes' => PageType::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = PageType::create($this->validated($request));

        return redirect()->route('admin.types.edit', $type)->with('status', 'Тип создан.');
    }

    public function edit(PageType $type): View
    {
        return view('admin.types.edit', [
            'type' => $type,
            'allTypes' => PageType::orderBy('sort')->get(),
        ]);
    }

    public function update(Request $request, PageType $type): RedirectResponse
    {
        $type->update($this->validated($request, $type));

        return redirect()->route('admin.types.edit', $type)->with('status', 'Сохранено.');
    }

    public function destroy(PageType $type): RedirectResponse
    {
        if ($type->pages()->withTrashed()->exists()) {
            return back()->withErrors(['type' => 'Нельзя удалить тип, у которого есть страницы.']);
        }

        $type->delete();

        return redirect()->route('admin.types.index')->with('status', 'Тип удалён.');
    }

    private function validated(Request $request, ?PageType $type = null): array
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/', Rule::unique('page_types')->ignore($type?->id)],
            'name' => ['required', 'string', 'max:80'],
            'name_plural' => ['required', 'string', 'max:80'],
            'url_prefix' => [
                'required', 'string', 'max:40', 'regex:/^[a-z0-9-]+$/',
                Rule::notIn(PageType::RESERVED_PREFIXES),
                Rule::unique('page_types')->ignore($type?->id),
            ],
            'layout' => ['required', Rule::in(array_keys(PageType::LAYOUTS))],
            'description' => ['nullable', 'string', 'max:2000'],
            'numbered' => ['boolean'],
            'number_format' => ['nullable', 'string', 'max:40'],
            'show_in_nav' => ['boolean'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'fields_json' => ['nullable', 'json'],
        ], [
            'key.regex' => 'Ключ: латиница, цифры и «_».',
            'url_prefix.regex' => 'Адрес раздела: латиница, цифры и дефис.',
            'url_prefix.not_in' => 'Этот адрес занят системой.',
        ]);

        $data['fields'] = $this->normalizeFields(json_decode($data['fields_json'] ?? '[]', true) ?: []);
        $data['numbered'] = $request->boolean('numbered');
        $data['show_in_nav'] = $request->boolean('show_in_nav');
        $data['sort'] = (int) ($data['sort'] ?? 0);
        unset($data['fields_json']);

        return $data;
    }

    private function normalizeFields(array $fields): array
    {
        $out = [];
        $seen = [];

        foreach ($fields as $i => $f) {
            $key = trim((string) ($f['key'] ?? ''));
            $label = trim((string) ($f['label'] ?? ''));

            if ($key === '' && $label === '') {
                continue;
            }
            if (! preg_match('/^[a-z0-9_]+$/', $key)) {
                throw ValidationException::withMessages(['fields_json' => 'Поле №'.($i + 1).": ключ «{$key}» — только латиница, цифры и «_»."]);
            }
            if (isset($seen[$key])) {
                throw ValidationException::withMessages(['fields_json' => "Ключ «{$key}» повторяется."]);
            }
            $seen[$key] = true;

            $fieldType = array_key_exists($f['type'] ?? '', PageType::FIELD_TYPES) ? $f['type'] : 'text';

            $out[] = array_filter([
                'key' => $key,
                'label' => $label ?: $key,
                'type' => $fieldType,
                'options' => $fieldType === 'select'
                    ? collect(is_array($f['options'] ?? null) ? $f['options'] : preg_split('/\r?\n/', (string) ($f['options'] ?? '')))
                        ->map(fn ($o) => trim((string) $o))->filter()->unique()->values()->all()
                    : null,
                'page_type' => in_array($fieldType, ['page', 'pages'], true) ? ($f['page_type'] ?? null ?: null) : null,
                'required' => ! empty($f['required']),
                'header' => ! empty($f['header']),
                'list' => ! empty($f['list']),
                'help' => trim((string) ($f['help'] ?? '')) ?: null,
            ], fn ($v) => $v !== null);
        }

        return $out;
    }
}
