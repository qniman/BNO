@extends('layouts.archive')

@section('title', 'Инциденты')

@section('content')
    <header class="mb-6 border-b border-bno-border pb-4">
        <p class="text-xs uppercase text-bno-muted">Хронология</p>
        <h1 class="font-display text-4xl uppercase tracking-[0.35em] text-bno-active">Инциденты</h1>
    </header>

    <div class="overflow-x-auto border border-bno-border bg-black">
        <table class="min-w-full divide-y divide-bno-border text-left text-xs uppercase">
            <thead class="bg-bno-panel text-bno-muted">
            <tr>
                <th class="px-3 py-2">Дата</th>
                <th class="px-3 py-2">Объект</th>
                <th class="px-3 py-2">Описание</th>
                <th class="px-3 py-2">Жертвы</th>
                <th class="px-3 py-2">Уровень</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-bno-border text-bno-archive">
            @foreach ($incidents as $incident)
                <tr>
                    <td class="px-3 py-2">{{ $incident->occurred_on?->format('d.m.Y') ?? '—' }}</td>
                    <td class="px-3 py-2">
                        @if ($incident->object)
                            <a href="{{ route('objects.show', $incident->object->slug) }}" class="text-bno-active hover:underline">{{ $incident->object->number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-3 py-2 normal-case">
                        <a href="{{ route('incidents.show', $incident) }}" class="hover:text-bno-active hover:underline">
                            {{ \Illuminate\Support\Str::limit($incident->title.' — '.$incident->description, 160) }}
                        </a>
                    </td>
                    <td class="px-3 py-2">{{ $incident->casualties ?? 'нет данных' }}</td>
                    <td class="px-3 py-2">{{ $incident->severity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-xs text-bno-muted">
        {{ $incidents->links() }}
    </div>
@endsection
