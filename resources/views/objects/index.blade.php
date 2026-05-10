@extends('layouts.archive')

@section('title', 'Архив объектов')

@section('content')
    <header class="mb-6 flex flex-col gap-4 border-b border-bno-border pb-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs uppercase text-bno-muted">Регистр объектов</p>
            <h1 class="font-display text-4xl uppercase tracking-[0.25em] text-bno-active">Каталог</h1>
            <p class="mt-2 max-w-2xl text-sm text-bno-archive">
                Отображаются записи в пределах вашего уровня допуска. Поля поиска используют регламентированную строковую выборку.
            </p>
        </div>
        <div class="text-xs uppercase text-bno-muted">
            Эффективный допуск: {{ \App\Support\ArchiveAccess::clearance() }}
        </div>
    </header>

    <form method="get" class="mb-6 grid gap-3 border border-bno-border bg-bno-panel p-4 md:grid-cols-4">
        <label class="text-[11px] uppercase text-bno-muted md:col-span-2">
            Поиск
            <input type="text" name="q" value="{{ $filters['search'] }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            Класс угрозы
            <input type="text" name="threat" value="{{ $filters['threat_class'] }}" placeholder="НС / ОП ..." class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <label class="text-[11px] uppercase text-bno-muted">
            Статус
            <input type="text" name="status" value="{{ $filters['status'] }}" class="mt-1 w-full border border-bno-border bg-black px-2 py-2 text-sm text-bno-archive">
        </label>
        <div class="md:col-span-4 flex flex-wrap gap-3 text-[11px] uppercase">
            <label class="flex items-center gap-2 text-bno-muted">
                Сортировка
                <select name="sort" class="border border-bno-border bg-black px-2 py-1 text-bno-archive">
                    <option value="number" @selected($filters['sort'] === 'number')>номер</option>
                    <option value="title" @selected($filters['sort'] === 'title')>название</option>
                    <option value="threat" @selected($filters['sort'] === 'threat')>угроза</option>
                </select>
            </label>
            <label class="flex items-center gap-2 text-bno-muted">
                Направление
                <select name="dir" class="border border-bno-border bg-black px-2 py-1 text-bno-archive">
                    <option value="asc" @selected($filters['dir'] === 'asc')>прямое</option>
                    <option value="desc" @selected($filters['dir'] === 'desc')>обратное</option>
                </select>
            </label>
            <button type="submit" class="border border-bno-active px-4 py-2 text-bno-active hover:bg-bno-active hover:text-black">Применить</button>
            <a href="{{ route('objects.index') }}" class="border border-dashed border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">Сброс</a>
        </div>
    </form>

    <div class="overflow-x-auto border border-bno-border bg-black">
        <table class="min-w-full divide-y divide-bno-border text-left text-xs uppercase">
            <thead class="bg-bno-panel text-bno-muted">
            <tr>
                <th class="px-3 py-2">Номер</th>
                <th class="px-3 py-2">Название</th>
                <th class="px-3 py-2">Статус</th>
                <th class="px-3 py-2">Класс угрозы</th>
                <th class="px-3 py-2">Допуск</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-bno-border text-bno-archive">
            @foreach ($objects as $dto)
                <tr class="hover:bg-bno-card">
                    <td class="px-3 py-2 font-display text-sm text-bno-active">
                        <a href="{{ route('objects.show', $dto->slug) }}" class="hover:underline">{{ $dto->number }}</a>
                    </td>
                    <td class="px-3 py-2 normal-case">{{ $dto->title }}</td>
                    <td class="px-3 py-2">{{ $dto->status }}</td>
                    <td class="px-3 py-2">{{ $dto->threatClass ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $dto->clearanceLevel }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-xs text-bno-muted">
        {{ $objects->links() }}
    </div>
@endsection
