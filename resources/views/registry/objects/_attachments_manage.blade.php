<div class="mt-8 border border-bno-border bg-bno-panel p-6">
    <p class="text-xs uppercase text-bno-muted">Приложения к объекту</p>
    <h2 class="font-display text-2xl uppercase tracking-[0.25em] text-bno-active">{{ $object->number }}</h2>
    <p class="mt-2 max-w-2xl text-xs uppercase text-bno-muted">
        Файлы сохраняются в <code class="text-bno-archive">storage/app/public</code>. Для отображения по URL выполните <code class="text-bno-archive">php artisan storage:link</code>.
    </p>

    <div class="mt-6 space-y-4">
        @forelse ($object->attachments as $att)
            <div class="flex flex-wrap items-start justify-between gap-4 border border-bno-border bg-black px-4 py-3 text-xs uppercase">
                <div>
                    <p class="font-display text-lg normal-case text-bno-active">{{ $att->label ?: 'Без подписи' }}</p>
                    <p class="mt-1 text-bno-muted">{{ $att->kind }} · допуск {{ $att->clearance_level }} · порядок {{ $att->sort_order }}</p>
                    <p class="mt-1 break-all text-[10px] normal-case text-bno-archive">{{ $att->path }}</p>
                </div>
                <form method="post" action="{{ route('registry.objects.attachments.destroy', [$object, $att]) }}" onsubmit="return confirm('Удалить вложение?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="border border-bno-alarm px-3 py-1 text-bno-alarm hover:bg-bno-alarm hover:text-black">Удалить</button>
                </form>
            </div>
        @empty
            <p class="text-xs uppercase text-bno-muted">Вложений нет.</p>
        @endforelse
    </div>

    <form method="post" action="{{ route('registry.objects.attachments.store', $object) }}" enctype="multipart/form-data" class="mt-8 grid gap-4 border border-dashed border-bno-border bg-black p-4 md:grid-cols-2">
        @csrf
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            файл
            <input type="file" name="file" required class="mt-1 w-full border border-bno-border bg-bno-panel px-2 py-2 text-sm normal-case text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            тип
            <select name="kind" class="mt-1 w-full border border-bno-border bg-bno-panel px-2 py-2 text-sm text-bno-archive">
                <option value="scan">scan</option>
                <option value="image">image</option>
                <option value="audio">audio</option>
                <option value="video">video</option>
            </select>
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            допуск 0–5
            <input type="number" name="clearance_level" min="0" max="5" value="1" required class="mt-1 w-full border border-bno-border bg-bno-panel px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            подпись
            <input type="text" name="label" class="mt-1 w-full border border-bno-border bg-bno-panel px-2 py-2 text-sm normal-case text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            порядок
            <input type="number" name="sort_order" min="0" value="0" class="mt-1 w-full border border-bno-border bg-bno-panel px-2 py-2 text-sm text-bno-archive">
        </label>
        <div class="flex items-end md:col-span-2">
            <button type="submit" class="border border-bno-active px-6 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Загрузить</button>
        </div>
    </form>
</div>
