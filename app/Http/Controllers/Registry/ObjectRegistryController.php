<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use App\Models\ObjectEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ObjectRegistryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage-registry');

        $objects = ObjectEntity::query()->orderBy('number')->paginate(40);

        return view('registry.objects.index', compact('objects'));
    }

    public function create(): View
    {
        Gate::authorize('manage-registry');

        return view('registry.objects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['slug']);

        ObjectEntity::query()->create($data);

        Cache::flush();

        return redirect()->route('registry.objects.index')->with('status', 'Запись зарегистрирована.');
    }

    public function edit(ObjectEntity $object): View
    {
        Gate::authorize('manage-registry');

        $object->load([
            'attachments' => fn ($q) => $q->orderBy('sort_order'),
            'appendices' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        return view('registry.objects.edit', compact('object'));
    }

    public function update(Request $request, ObjectEntity $object): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['slug']);

        $object->update($data);

        Cache::flush();

        return redirect()->route('registry.objects.index')->with('status', 'Запись обновлена.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:32'],
            'slug' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:64'],
            'threat_class' => ['nullable', 'string', 'max:16'],
            'anomaly_type' => ['nullable', 'string', 'max:128'],
            'containment_level' => ['nullable', 'string', 'max:16'],
            'stability_index' => ['nullable', 'string', 'max:16'],
            'psi_index' => ['nullable', 'string', 'max:16'],
            'amnestic_index' => ['nullable', 'string', 'max:16'],
            'interaction_index' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string'],
            'containment_protocol' => ['nullable', 'string'],
            'history' => ['nullable', 'string'],
            'clearance_level' => ['required', 'integer', 'min:0', 'max:5'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_hidden'] = $request->boolean('is_hidden');
        $data['public_archive'] = $request->boolean('public_archive');

        return $data;
    }
}
