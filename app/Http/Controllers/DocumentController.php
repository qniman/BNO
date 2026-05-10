<?php

namespace App\Http\Controllers;

use App\Events\ArchiveAccessLogged;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentRevision;
use App\Services\ArchiveRedactor;
use App\Services\ObjectRegistryService;
use App\Support\ArchiveAccess;
use App\Support\ArchiveSearchGate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->filled('q')) {
            $raw = $request->string('q')->toString();
            $restricted = ArchiveSearchGate::restrictedMessage($raw);
            if ($restricted !== null) {
                event(new ArchiveAccessLogged(
                    userId: auth()->id(),
                    action: 'restricted_search_documents',
                    subjectType: null,
                    subjectId: null,
                    meta: ['q' => $raw],
                    ip: request()->ip(),
                ));

                return view('archive.restricted-query', [
                    'message' => $restricted,
                    'query' => $raw,
                    'context' => 'documents',
                ]);
            }
        }

        $c = ArchiveAccess::clearance();

        /** @var LengthAwarePaginator<int, Document> $documents */
        $documents = Document::query()
            ->where('clearance_level', '<=', $c)
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $t = '%'.$request->string('q')->toString().'%';
                $q->where(function ($qq) use ($t) {
                    $qq->where('title', 'like', $t)->orWhere('content', 'like', $t);
                });
            })
            ->orderByDesc('published_at')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('documents.index', compact('documents'));
    }

    public function show(string $slug, ObjectRegistryService $objects): View
    {
        $document = Document::query()->where('slug', $slug)->firstOrFail();

        $this->authorize('view', $document);

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'document_view',
            subjectType: Document::class,
            subjectId: $document->id,
            meta: ['slug' => $document->slug],
            ip: request()->ip(),
        ));

        $body = ArchiveRedactor::rich($document->content ?? '');

        if ($document->volatile) {
            $salt = crc32(request()->session()->getId().now()->format('YmdHi'));
            $corruptions = [
                'ВНИМАНИЕ: контрольная сумма текста не совпадает с эталоном '.($salt % 997),
                'Фрагмент повреждён при последней выгрузке. Использовать копию из регистратуры.',
                null,
            ];
            $corruption = $corruptions[$salt % 3];
        } else {
            $corruption = null;
        }

        $stampMeta = $document->stamp
            ? config('bno.document_stamps.'.$document->stamp)
            : null;

        $referencedObject = null;
        if ($document->references_object_slug) {
            $referencedObject = $objects->findForViewer($document->references_object_slug);
        }

        $revisionHistory = DocumentRevision::query()
            ->where('document_id', $document->id)
            ->with('editor')
            ->orderByDesc('revised_at')
            ->limit(12)
            ->get();

        $viewsLastDay = AuditLog::query()
            ->where('subject_type', Document::class)
            ->where('subject_id', $document->id)
            ->where('action', 'document_view')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $lastView = AuditLog::query()
            ->where('subject_type', Document::class)
            ->where('subject_id', $document->id)
            ->where('action', 'document_view')
            ->with('user')
            ->orderByDesc('id')
            ->skip(1)
            ->first();

        return view('documents.show', compact(
            'document',
            'body',
            'corruption',
            'stampMeta',
            'referencedObject',
            'revisionHistory',
            'viewsLastDay',
            'lastView',
        ));
    }
}
