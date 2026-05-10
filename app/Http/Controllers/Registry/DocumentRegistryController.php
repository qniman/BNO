<?php

namespace App\Http\Controllers\Registry;

use App\Events\ArchiveAccessLogged;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentRevision;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentRegistryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-registry');

        $documents = Document::query()
            ->with('author')
            ->orderByDesc('updated_at')
            ->paginate(35);

        return view('registry.documents.index', compact('documents'));
    }

    public function create(): View
    {
        Gate::authorize('manage-registry');

        return view('registry.documents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $data = $this->validatedDocument($request);
        unset($data['change_reason']);
        $data['slug'] = Str::slug($data['slug']);
        $data['references_object_slug'] = $this->normalizeReferenceSlug($data['references_object_slug'] ?? null);
        if (isset($data['revision_label']) && trim((string) $data['revision_label']) === '') {
            unset($data['revision_label']);
        }
        $data['author_id'] = $request->user()->id;

        $document = Document::query()->create($data);

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_document_store',
            subjectType: Document::class,
            subjectId: $document->id,
            meta: ['slug' => $document->slug],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.documents.index')->with('status', 'Документ добавлен.');
    }

    public function edit(Document $document): View
    {
        Gate::authorize('manage-registry');

        return view('registry.documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $validated = $this->validatedDocument($request, $document->id);
        $reason = $validated['change_reason'];
        unset($validated['change_reason']);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['references_object_slug'] = $this->normalizeReferenceSlug($validated['references_object_slug'] ?? null);

        if ($this->documentNeedsRevisionSnapshot($document, $validated)) {
            DocumentRevision::query()->create([
                'document_id' => $document->id,
                'revision_label' => $document->revision_label ?: '1.0',
                'revised_at' => now(),
                'editor_id' => auth()->id(),
                'change_reason' => $reason !== '' ? $reason : 'Внесение изменений через узел регистратуры.',
                'content_snapshot' => $document->content,
            ]);
            $validated['revision_label'] = self::bumpRevisionLabel($document->revision_label ?: '1.0');
        }

        $document->update($validated);

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_document_update',
            subjectType: Document::class,
            subjectId: $document->id,
            meta: ['slug' => $document->slug],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.documents.index')->with('status', 'Документ обновлён.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $slug = $document->slug;
        $id = $document->id;
        $document->delete();

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_document_destroy',
            subjectType: Document::class,
            subjectId: $id,
            meta: ['slug' => $slug],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.documents.index')->with('status', 'Документ удалён.');
    }

    /**
     * @param  array<string, mixed>  $incoming
     */
    private function documentNeedsRevisionSnapshot(Document $document, array $incoming): bool
    {
        $keys = [
            'title', 'slug', 'type', 'content', 'classification', 'clearance_level',
            'published_at', 'is_redacted', 'volatile',
            'stamp', 'registration_number', 'routing_note', 'acknowledgment_stub',
            'marginalia', 'corruption_level', 'chronology_anomaly', 'leaked_publish',
            'references_object_slug',
        ];

        foreach ($keys as $key) {
            if (! array_key_exists($key, $incoming)) {
                continue;
            }
            if ($this->normalizeComparable($document->getAttribute($key), $key)
                !== $this->normalizeComparable($incoming[$key], $key)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeComparable(mixed $value, string $key): string
    {
        return match ($key) {
            'published_at' => $value instanceof CarbonInterface
                ? $value->format('Y-m-d H:i:s')
                : ($value ? \Carbon\Carbon::parse((string) $value)->format('Y-m-d H:i:s') : ''),
            'is_redacted', 'volatile', 'chronology_anomaly', 'leaked_publish' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'clearance_level', 'corruption_level' => (string) (int) ($value ?? 0),
            default => trim((string) ($value ?? '')),
        };
    }

    private static function bumpRevisionLabel(string $label): string
    {
        $label = trim($label);
        if (preg_match('/^(\d+)\.(\d+)$/', $label, $m)) {
            return $m[1].'.'.((int) $m[2] + 1);
        }

        return '1.1';
    }

    private function normalizeReferenceSlug(?string $slug): ?string
    {
        if ($slug === null || trim($slug) === '') {
            return null;
        }

        return Str::slug($slug);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedDocument(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = Rule::unique('documents', 'slug');
        if ($ignoreId !== null) {
            $slugRule = $slugRule->ignore($ignoreId);
        }

        $stampKeys = array_keys(config('bno.document_stamps', []));

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $slugRule],
            'type' => ['required', 'string', 'max:64'],
            'content' => ['nullable', 'string'],
            'classification' => ['nullable', 'string', 'max:64'],
            'clearance_level' => ['required', 'integer', 'min:0', 'max:5'],
            'published_at' => ['nullable', 'date'],
            'stamp' => ['nullable', 'string', 'max:64', Rule::in($stampKeys)],
            'registration_number' => ['nullable', 'string', 'max:128'],
            'routing_note' => ['nullable', 'string'],
            'acknowledgment_stub' => ['nullable', 'string', 'max:255'],
            'marginalia' => ['nullable', 'string'],
            'corruption_level' => ['required', 'integer', 'min:0', 'max:3'],
            'references_object_slug' => ['nullable', 'string', 'max:64'],
        ];

        if ($ignoreId !== null) {
            $rules['change_reason'] = ['required', 'string', 'max:2000'];
        } else {
            $rules['change_reason'] = ['nullable', 'string', 'max:2000'];
            $rules['revision_label'] = ['nullable', 'string', 'max:16'];
        }

        $data = $request->validate($rules);

        $data['is_redacted'] = $request->boolean('is_redacted');
        $data['volatile'] = $request->boolean('volatile');
        $data['chronology_anomaly'] = $request->boolean('chronology_anomaly');
        $data['leaked_publish'] = $request->boolean('leaked_publish');

        if (($data['stamp'] ?? '') === '') {
            $data['stamp'] = null;
        }

        return $data;
    }
}
