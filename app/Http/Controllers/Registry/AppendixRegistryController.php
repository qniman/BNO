<?php

namespace App\Http\Controllers\Registry;

use App\Events\ArchiveAccessLogged;
use App\Http\Controllers\Controller;
use App\Models\Appendix;
use App\Models\ObjectEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppendixRegistryController extends Controller
{
    public function store(Request $request, ObjectEntity $object): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $data = $this->validatedAppendix($request);
        $data['object_entity_id'] = $object->id;

        $appendix = Appendix::query()->create($data);

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_appendix_store',
            subjectType: Appendix::class,
            subjectId: $appendix->id,
            meta: ['object_id' => $object->id, 'label' => $appendix->label],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.objects.edit', $object)->with('status', 'Аддендум добавлен.');
    }

    public function update(Request $request, ObjectEntity $object, Appendix $appendix): RedirectResponse
    {
        Gate::authorize('manage-registry');

        abort_unless($appendix->object_entity_id === $object->id, 404);

        $data = $this->validatedAppendix($request);
        $appendix->update($data);

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_appendix_update',
            subjectType: Appendix::class,
            subjectId: $appendix->id,
            meta: ['object_id' => $object->id],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.objects.edit', $object)->with('status', 'Аддендум обновлён.');
    }

    public function destroy(ObjectEntity $object, Appendix $appendix): RedirectResponse
    {
        Gate::authorize('manage-registry');

        abort_unless($appendix->object_entity_id === $object->id, 404);

        $id = $appendix->id;
        $appendix->delete();

        event(new ArchiveAccessLogged(
            userId: auth()->id(),
            action: 'registry_appendix_destroy',
            subjectType: Appendix::class,
            subjectId: $id,
            meta: ['object_id' => $object->id],
            ip: request()->ip(),
        ));

        return redirect()->route('registry.objects.edit', $object)->with('status', 'Аддендум удалён.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAppendix(Request $request): array
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'clearance_level' => ['required', 'integer', 'min:0', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $data['corrupted'] = $request->boolean('corrupted');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
