@php
    /** @var \App\Models\ObjectEntity|null $object */
@endphp

<div class="border border-bno-border bg-bno-panel p-6">
    <p class="text-xs uppercase text-bno-muted">Регистратура</p>
    <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">{{ $heading }}</h1>

    <form method="post" action="{{ $action }}" class="mt-6 grid gap-4 md:grid-cols-2">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <label class="text-[11px] uppercase text-bno-muted md:col-span-1">
            номер
            <input name="number" value="{{ old('number', $object->number ?? '') }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted md:col-span-1">
            slug
            <input name="slug" value="{{ old('slug', $object->slug ?? '') }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            название
            <input name="title" value="{{ old('title', $object->title ?? '') }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted">
            статус
            <input name="status" value="{{ old('status', $object->status ?? 'АКТИВЕН') }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            допуск 0–5
            <input type="number" name="clearance_level" min="0" max="5" value="{{ old('clearance_level', $object->clearance_level ?? 1) }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted">
            класс угрозы
            <input name="threat_class" value="{{ old('threat_class', $object->threat_class ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            тип аномалии
            <input name="anomaly_type" value="{{ old('anomaly_type', $object->anomaly_type ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted">
            протокол содержания (код)
            <input name="containment_level" value="{{ old('containment_level', $object->containment_level ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            индекс стабильности
            <input name="stability_index" value="{{ old('stability_index', $object->stability_index ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            ПИ
            <input name="psi_index" value="{{ old('psi_index', $object->psi_index ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            Ам
            <input name="amnestic_index" value="{{ old('amnestic_index', $object->amnestic_index ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            Вз
            <input name="interaction_index" value="{{ old('interaction_index', $object->interaction_index ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            описание (Markdown)
            <x-registry.markdown-editor
                name="description"
                :value="old('description', $object->description ?? '')"
                placeholder="Описание объекта…"
                :min-height="220"
                class="mt-1"
            />
        </label>
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            протокол содержания (текст, Markdown)
            <x-registry.markdown-editor
                name="containment_protocol"
                :value="old('containment_protocol', $object->containment_protocol ?? '')"
                placeholder="Режим хранения, ограничения…"
                :min-height="200"
                class="mt-1"
            />
        </label>
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            история (Markdown)
            <x-registry.markdown-editor
                name="history"
                :value="old('history', $object->history ?? '')"
                placeholder="Происхождение, цепочка событий…"
                :min-height="200"
                class="mt-1"
            />
        </label>

        <div class="flex flex-wrap gap-4 text-[11px] uppercase text-bno-muted md:col-span-2">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $object->is_active ?? true))>
                активна
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_hidden" value="1" @checked(old('is_hidden', $object->is_hidden ?? false))>
                скрытая запись
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="public_archive" value="1" @checked(old('public_archive', $object->public_archive ?? false))>
                публичный флаг (справочно)
            </label>
        </div>

        <div class="md:col-span-2 flex gap-3">
            <button type="submit" class="border border-bno-active px-6 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Сохранить</button>
            <a href="{{ route('registry.objects.index') }}" class="border border-bno-border px-6 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Отмена</a>
        </div>
    </form>
</div>
