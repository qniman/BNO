@php
    /** @var \App\Models\Incident|null $incident */
@endphp

<div class="border border-bno-border bg-bno-panel p-6">
    <p class="text-xs uppercase text-bno-muted">Регистратура</p>
    <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">{{ $heading }}</h1>

    <form method="post" action="{{ $action }}" class="mt-6 grid gap-4 md:grid-cols-2">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            связанный объект
            <select name="object_entity_id" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
                <option value="">— нет —</option>
                @foreach ($objects as $obj)
                    <option value="{{ $obj->id }}" @selected((string) old('object_entity_id', $incident?->object_entity_id ?? '') === (string) $obj->id)>
                        {{ $obj->number }} — {{ $obj->title }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            название
            <input type="text" name="title" value="{{ old('title', $incident?->title ?? '') }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted">
            дата
            <input type="date" name="occurred_on" value="{{ old('occurred_on', $incident?->occurred_on ? $incident->occurred_on->format('Y-m-d') : '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            допуск 0–5
            <input type="number" name="clearance_level" min="0" max="5" value="{{ old('clearance_level', $incident?->clearance_level ?? 1) }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted">
            уровень угрозы / последствия
            <input type="text" name="severity" value="{{ old('severity', $incident?->severity ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            жертвы / потери
            <input type="text" name="casualties" value="{{ old('casualties', $incident?->casualties ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            локация
            <input type="text" name="location" value="{{ old('location', $incident?->location ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            описание (Markdown)
            <x-registry.markdown-editor
                name="description"
                :value="old('description', $incident?->description ?? '')"
                placeholder="Хронология, контекст, действия…"
                :min-height="260"
                class="mt-1"
            />
        </label>

        <div class="md:col-span-2 flex gap-3">
            <button type="submit" class="border border-bno-active px-6 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Сохранить</button>
            <a href="{{ route('registry.incidents.index') }}" class="border border-bno-border px-6 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Отмена</a>
        </div>
    </form>
</div>
