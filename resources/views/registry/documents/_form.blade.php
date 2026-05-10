@php
    /** @var \App\Models\Document|null $document */
    $isEdit = ($method ?? 'POST') !== 'POST';
@endphp

<div class="border border-bno-border bg-bno-panel p-6">
    <p class="text-xs uppercase text-bno-muted">Регистратура</p>
    <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">{{ $heading }}</h1>

    <form method="post" action="{{ $action }}" class="mt-6 grid gap-4 md:grid-cols-2">
        @csrf
        @if (($method ?? 'POST') !== 'POST')
            @method($method)
        @endif

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            заголовок
            <input type="text" name="title" value="{{ old('title', $document->title ?? '') }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            slug (латиница)
            <input type="text" name="slug" value="{{ old('slug', $document->slug ?? '') }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            тип
            <select name="type" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
                @foreach (config('bno.document_types') as $type)
                    <option value="{{ $type }}" @selected(old('type', $document->type ?? 'протокол') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            классификация
            <input type="text" name="classification" value="{{ old('classification', $document->classification ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            допуск 0–5
            <input type="number" name="clearance_level" min="0" max="5" value="{{ old('clearance_level', $document->clearance_level ?? 1) }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            дата публикации
            <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($document) && $document->published_at ? $document->published_at->timezone(config('app.timezone'))->format('Y-m-d\TH:i') : '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        @if (! $isEdit)
            <label class="text-[11px] uppercase text-bno-muted">
                начальная редакция (необязательно)
                <input type="text" name="revision_label" value="{{ old('revision_label', $document->revision_label ?? '1.0') }}" maxlength="16" placeholder="1.0" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm font-mono text-bno-archive">
            </label>
        @else
            <div class="border border-dashed border-bno-border bg-black/30 px-3 py-2 text-[11px] uppercase text-bno-muted md:col-span-1">
                текущая редакция
                <p class="mt-1 font-mono text-sm normal-case text-bno-active">{{ $document->revision_label ?? '1.0' }}</p>
                <p class="mt-2 text-[10px] normal-case text-bno-muted">При сохранении с изменениями номер редакции будет увеличен автоматически.</p>
            </div>
        @endif

        <label class="text-[11px] uppercase text-bno-muted">
            гриф / пометка
            <select name="stamp" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
                <option value="">—</option>
                @foreach (config('bno.document_stamps') as $key => $meta)
                    <option value="{{ $key }}" @selected(old('stamp', $document->stamp ?? '') === $key)>{{ $meta['label'] }}</option>
                @endforeach
            </select>
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            регистрационный номер
            <input type="text" name="registration_number" value="{{ old('registration_number', $document->registration_number ?? '') }}" placeholder="АР/04-11/2003-17" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm font-mono text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            маршрут согласования
            <x-registry.markdown-editor
                name="routing_note"
                :value="old('routing_note', $document->routing_note ?? '')"
                placeholder="Сектор → архив → …"
                :min-height="108"
                class="mt-1"
            />
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            отметка «ознакомлен» / подписной блок
            <input type="text" name="acknowledgment_stub" value="{{ old('acknowledgment_stub', $document->acknowledgment_stub ?? '') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            маргиналии и следы правки (видны в узле)
            <x-registry.markdown-editor
                name="marginalia"
                :value="old('marginalia', $document->marginalia ?? '')"
                placeholder="Комментарии редактора, следы удаления…"
                :min-height="140"
                class="mt-1"
            />
        </label>

        <label class="text-[11px] uppercase text-bno-muted">
            повреждение записи (0–3)
            <input type="number" name="corruption_level" min="0" max="3" value="{{ old('corruption_level', $document->corruption_level ?? 0) }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>

        <label class="text-[11px] uppercase text-bno-muted">
            slug связанного объекта (опционально)
            <input type="text" name="references_object_slug" value="{{ old('references_object_slug', $document->references_object_slug ?? '') }}" placeholder="bno-524" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm font-mono text-bno-archive">
        </label>

        @if ($isEdit)
            <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
                причина изменения редакции (обязательно)
                <textarea name="change_reason" rows="2" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">{{ old('change_reason', '') }}</textarea>
            </label>
        @endif

        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            текст (Markdown)
            <x-registry.markdown-editor
                name="content"
                :value="old('content', $document->content ?? '')"
                placeholder="Основной текст документа…"
                :min-height="340"
                class="mt-1"
            />
        </label>

        <div class="flex flex-wrap gap-4 text-[11px] uppercase text-bno-muted md:col-span-2">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_redacted" value="1" @checked(old('is_redacted', $document->is_redacted ?? false))>
                редактор [[[REDACTED]]]
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="volatile" value="1" @checked(old('volatile', $document->volatile ?? false))>
                нестабильная запись
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="chronology_anomaly" value="1" @checked(old('chronology_anomaly', $document->chronology_anomaly ?? false))>
                аномалия хронологии (метка в узле)
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="leaked_publish" value="1" @checked(old('leaked_publish', $document->leaked_publish ?? false))>
                признак ошибочной публикации
            </label>
        </div>

        <div class="md:col-span-2 flex gap-3">
            <button type="submit" class="border border-bno-active px-6 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Сохранить</button>
            <a href="{{ route('registry.documents.index') }}" class="border border-bno-border px-6 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Отмена</a>
        </div>
    </form>
</div>
