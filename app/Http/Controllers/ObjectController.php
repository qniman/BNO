<?php

namespace App\Http\Controllers;

use App\Events\ArchiveAccessLogged;
use App\Models\ObjectEntity;
use App\Services\AnomalyPresentationService;
use App\Support\ArchiveAccess;
use App\Support\ArchiveSearchGate;
use App\Services\ObjectRegistryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ObjectController extends Controller
{
    public function index(Request $request, ObjectRegistryService $registry): View
    {
        if ($request->filled('q')) {
            $raw = $request->string('q')->toString();
            $restricted = ArchiveSearchGate::restrictedMessage($raw);
            if ($restricted !== null) {
                event(new ArchiveAccessLogged(
                    userId: auth()->id(),
                    action: 'restricted_search_objects',
                    subjectType: null,
                    subjectId: null,
                    meta: ['q' => $raw],
                    ip: request()->ip(),
                ));

                return view('archive.restricted-query', [
                    'message' => $restricted,
                    'query' => $raw,
                    'context' => 'objects',
                ]);
            }
        }

        $filters = [
            'search' => $request->string('q')->toString(),
            'threat_class' => $request->string('threat')->toString(),
            'status' => $request->string('status')->toString(),
            'sort' => $request->string('sort')->toString() ?: 'number',
            'dir' => $request->string('dir')->toString() ?: 'asc',
        ];

        $objects = $registry->list($filters);

        return view('objects.index', [
            'objects' => $objects,
            'filters' => $filters,
        ]);
    }

    public function show(
        string $slug,
        ObjectRegistryService $registry,
        AnomalyPresentationService $presentation,
    ): View {
        if ($slug === 'bno-001') {
            $ghost = $presentation->ghostRecordState($slug);

            event(new ArchiveAccessLogged(
                userId: auth()->id(),
                action: 'ghost_record_view',
                subjectType: ObjectEntity::class,
                subjectId: null,
                meta: ['slug' => $slug, 'mode' => $ghost['mode']],
                ip: request()->ip(),
            ));

            return view('objects.ghost_bno001', ['ghost' => $ghost]);
        }

        $object = $registry->findForViewer($slug);

        abort_if(! $object, 404);

        $this->authorize('view', $object);

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'object_view',
            subjectType: ObjectEntity::class,
            subjectId: $object->id,
            meta: ['slug' => $object->slug],
            ip: request()->ip(),
        ));

        $c = ArchiveAccess::clearance();

        $object->load([
            'incidents' => fn ($q) => $q->where('clearance_level', '<=', $c)->orderByDesc('occurred_on'),
            'observationLogs' => fn ($q) => $q->where('clearance_level', '<=', $c)->orderByDesc('logged_at'),
            'attachments' => fn ($q) => $q->where('clearance_level', '<=', $c)->orderBy('sort_order'),
            'appendices' => fn ($q) => $q->where('clearance_level', '<=', $c)->orderBy('sort_order'),
        ]);

        $presentationHints = $presentation->forObject($object);

        $linkedObjects = $object->linkedObjects()
            ->wherePivot('clearance_level', '<=', $c)
            ->where('object_entities.clearance_level', '<=', $c)
            ->where('object_entities.is_active', true)
            ->when($c < 5, fn ($q) => $q->where('object_entities.is_hidden', false))
            ->orderBy('object_entities.number')
            ->get();

        return view('objects.show', [
            'object' => $object,
            'presentationHints' => $presentationHints,
            'linkedObjects' => $linkedObjects,
        ]);
    }
}
