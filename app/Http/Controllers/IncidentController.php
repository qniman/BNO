<?php

namespace App\Http\Controllers;

use App\Events\ArchiveAccessLogged;
use App\Models\Incident;
use App\Support\ArchiveAccess;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        $c = ArchiveAccess::clearance();

        $incidents = Incident::query()
            ->with('object')
            ->where('clearance_level', '<=', $c)
            ->orderByDesc('occurred_on')
            ->paginate(25)
            ->withQueryString();

        return view('incidents.index', compact('incidents'));
    }

    public function show(Incident $incident): View
    {
        $this->authorize('view', $incident);

        $incident->load('object');

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'incident_view',
            subjectType: Incident::class,
            subjectId: $incident->id,
            meta: ['title' => $incident->title],
            ip: request()->ip(),
        ));

        return view('incidents.show', compact('incident'));
    }
}
