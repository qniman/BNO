<?php

namespace App\Http\Controllers\Registry;

use App\Http\Controllers\Controller;
use App\Models\ObjectAttachment;
use App\Models\ObjectEntity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ObjectAttachmentController extends Controller
{
    public function store(Request $request, ObjectEntity $object): RedirectResponse
    {
        Gate::authorize('manage-registry');

        $data = $request->validate([
            'kind' => ['required', 'in:image,audio,video,scan'],
            'label' => ['nullable', 'string', 'max:255'],
            'clearance_level' => ['required', 'integer', 'min:0', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $mimeRule = match ($data['kind']) {
            'image' => 'mimes:jpeg,jpg,png,gif,webp,svg',
            'scan' => 'mimes:jpeg,jpg,png,pdf',
            'audio' => 'mimes:mp3,wav,ogg,m4a,aac,opus',
            'video' => 'mimes:mp4,webm,mov',
        };

        $request->validate([
            'file' => ['required', 'file', 'max:15360', $mimeRule],
        ]);

        $path = $request->file('file')->store('attachments/'.$object->id, 'public');

        $object->attachments()->create([
            'label' => $data['label'],
            'kind' => $data['kind'],
            'path' => $path,
            'clearance_level' => $data['clearance_level'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('registry.objects.edit', $object)->with('status', 'Вложение добавлено.');
    }

    public function destroy(ObjectEntity $object, ObjectAttachment $attachment): RedirectResponse
    {
        Gate::authorize('manage-registry');

        abort_unless($attachment->object_entity_id === $object->id, 404);

        if (! str_starts_with($attachment->path, '/') && ! str_starts_with($attachment->path, 'http://') && ! str_starts_with($attachment->path, 'https://')) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        return redirect()->route('registry.objects.edit', $object)->with('status', 'Вложение удалено.');
    }
}
