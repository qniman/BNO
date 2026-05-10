@extends('layouts.archive')

@section('title', 'Регистратура · инциденты')

@section('content')
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-bno-border pb-4">
        <div>
            <p class="text-xs uppercase text-bno-muted">Регистратура</p>
            <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">Инциденты</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('registry.incidents.create') }}" class="border border-bno-active px-4 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Новая запись</a>
            <a href="{{ route('registry.dashboard') }}" class="border border-bno-border px-4 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Узел</a>
        </div>
    </header>

    @if (session('status'))
        <div class="mb-4 border border-bno-border bg-bno-panel px-4 py-3 text-xs uppercase text-bno-active">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto border border-bno-border bg-black">
        <table class="min-w-full divide-y divide-bno-border text-left text-[11px] uppercase">
            <thead class="bg-bno-panel text-bno-muted">
            <tr>
                <th class="px-3 py-2">Дата</th>
                <th class="px-3 py-2">Объект</th>
                <th class="px-3 py-2">Название</th>
                <th class="px-3 py-2">Допуск</th>
                <th class="px-3 py-2"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-bno-border text-bno-archive">
            @foreach ($incidents as $incident)
                <tr class="align-top">
                    <td class="px-3 py-2">{{ $incident->occurred_on?->format('d.m.Y') ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $incident->object?->number ?? '—' }}</td>
                    <td class="px-3 py-2 normal-case">{{ \Illuminate\Support\Str::limit($incident->title, 80) }}</td>
                    <td class="px-3 py-2">{{ $incident->clearance_level }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <a href="{{ route('registry.incidents.edit', $incident) }}" class="text-bno-muted hover:text-bno-active">Правка</a>
                        <form method="post" action="{{ route('registry.incidents.destroy', $incident) }}" class="inline" onsubmit="return confirm('Удалить инцидент?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ml-2 text-bno-alarm hover:underline">×</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-xs text-bno-muted">{{ $incidents->links() }}</div>
@endsection
