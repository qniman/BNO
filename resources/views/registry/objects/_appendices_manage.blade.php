<div class="mt-8 border border-bno-border bg-black p-6">
    <p class="text-xs uppercase text-bno-muted">Аддендумы объекта</p>
    <h2 class="font-display text-2xl uppercase tracking-[0.25em] text-bno-active">{{ $object->number }}</h2>

    <div class="mt-6 space-y-8">
        @foreach ($object->appendices as $app)
            <div class="border border-dashed border-bno-border bg-bno-panel p-4">
                <form method="post" action="{{ route('registry.objects.appendices.update', [$object, $app]) }}" class="grid gap-3 md:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
                        метка
                        <input type="text" name="label" value="{{ $app->label }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
                    </label>
                    <label class="text-[11px] uppercase text-bno-muted">
                        допуск
                        <input type="number" name="clearance_level" min="0" max="5" value="{{ $app->clearance_level }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
                    </label>
                    <label class="text-[11px] uppercase text-bno-muted">
                        порядок
                        <input type="number" name="sort_order" min="0" value="{{ $app->sort_order }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
                    </label>
                    <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
                        текст (Markdown)
                        <x-registry.markdown-editor
                            name="body"
                            :value="$app->body"
                            placeholder="Текст аддендума…"
                            :min-height="180"
                            class="mt-1"
                        />
                    </label>
                    <label class="flex items-center gap-2 text-[11px] uppercase text-bno-muted md:col-span-2">
                        <input type="checkbox" name="corrupted" value="1" @checked($app->corrupted)>
                        помечено как повреждённое (ред.)
                    </label>
                    <div class="flex flex-wrap gap-2 md:col-span-2">
                        <button type="submit" class="border border-bno-active px-4 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Обновить</button>
                    </div>
                </form>
                <form method="post" action="{{ route('registry.objects.appendices.destroy', [$object, $app]) }}" class="mt-3 inline" onsubmit="return confirm('Удалить аддендум?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="border border-bno-alarm px-4 py-2 text-xs uppercase text-bno-alarm hover:bg-bno-alarm hover:text-black">Удалить аддендум</button>
                </form>
            </div>
        @endforeach
    </div>

    <form method="post" action="{{ route('registry.objects.appendices.store', $object) }}" class="mt-8 grid gap-3 border border-bno-border bg-bno-panel p-4 md:grid-cols-2">
        @csrf
        <p class="md:col-span-2 text-xs uppercase text-bno-muted">Новый аддендум</p>
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            метка
            <input type="text" name="label" value="{{ old('label') }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            допуск
            <input type="number" name="clearance_level" min="0" max="5" value="{{ old('clearance_level', 2) }}" required class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            порядок
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', 0) }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            текст (Markdown)
            <x-registry.markdown-editor
                name="body"
                :value="old('body')"
                placeholder="Текст аддендума…"
                :min-height="180"
                class="mt-1"
            />
        </label>
        <label class="flex items-center gap-2 text-[11px] uppercase text-bno-muted md:col-span-2">
            <input type="checkbox" name="corrupted" value="1" @checked(old('corrupted'))>
            повреждённая запись
        </label>
        <button type="submit" class="border border-bno-active px-4 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black md:col-span-2">Добавить</button>
    </form>
</div>
