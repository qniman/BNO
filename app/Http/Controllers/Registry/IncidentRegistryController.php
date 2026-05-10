<?php

namespace App\Http\Controllers\Registry;

use App\Events\ArchiveAccessLogged;
use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\ObjectEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class IncidentRegistryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-registry');

        $incidents = Incident::query()
            ->with('object')
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(35);

        return view('registry.incidents.index', compact('incidents'));
    }

    public function create(): View
    {
        Gate::authorize('manage-registry');

        $objects = ObjectEntity::query()->orderBy('number')->get(['id', 'number', 'title']);

        return view('registry.incidents.create', compact('objects'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $data = $this->validatedIncident($request);

        $incident = Incident::query()->create($data);

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_incident_store',
            subjectType: Incident::class,
            subjectId: $incident->id,
            meta: ['title' => $incident->title],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.incidents.index')->with('status', 'Инцидент зарегистрирован.');
    }

    public function edit(Incident $incident): View
    {
        Gate::authorize('manage-registry');

        $objects = ObjectEntity::query()->orderBy('number')->get(['id', 'number', 'title']);

        return view('registry.incidents.edit', compact('incident', 'objects'));
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $incident->update($this->validatedIncident($request));

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_incident_update',
            subjectType: Incident::class,
            subjectId: $incident->id,
            meta: ['title' => $incident->title],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.incidents.index')->with('status', 'Инцидент обновлён.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $title = $incident->title;
        $id = $incident->id;
        $incident->delete();

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_incident_destroy',
            subjectType: Incident::class,
            subjectId: $id,
            meta: ['title' => $title],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.incidents.index')->with('status', 'Инцидент удалён.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedIncident(Request $request): array
    {
        $request->merge([
            'object_entity_id' => $request->filled('object_entity_id') ? $request->integer('object_entity_id') : null,
        ]);

        return $request->validate([
            'object_entity_id' => ['nullable', 'integer', 'exists:object_entities,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['nullable', 'string', 'max:32'],
            'casualties' => ['nullable', 'string', 'max:128'],
            'occurred_on' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'clearance_level' => ['required', 'integer', 'min:0', 'max:5'],
        ]);
    }
}
