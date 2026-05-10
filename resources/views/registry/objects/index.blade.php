@extends('layouts.archive')

@section('title', 'Регистратура · объекты')

@section('content')
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-bno-border pb-4">
        <div>
            <p class="text-xs uppercase text-bno-muted">Регистратура</p>
            <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">Объекты</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('registry.audit.index') }}" class="border border-bno-border px-4 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Журнал аудита</a>
            <a href="{{ route('registry.documents.index') }}" class="border border-bno-border px-4 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Документы</a>
            <a href="{{ route('registry.incidents.index') }}" class="border border-bno-border px-4 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Инциденты</a>
            <a href="{{ route('registry.objects.create') }}" class="border border-bno-active px-4 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Создать</a>
        </div>
    </header>

    @if (session('status'))
        <div class="mb-4 border border-bno-border bg-bno-panel px-4 py-3 text-xs uppercase text-bno-active">
            {{ session('status') }}
        </div>
    @endif

    <div class="overflow-x-auto border border-bno-border bg-black">
        <table class="min-w-full divide-y divide-bno-border text-left text-xs uppercase">
            <thead class="bg-bno-panel text-bno-muted">
            <tr>
                <th class="px-3 py-2">Номер</th>
                <th class="px-3 py-2">Slug</th>
                <th class="px-3 py-2">Статус</th>
                <th class="px-3 py-2">Допуск</th>
                <th class="px-3 py-2"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-bno-border text-bno-archive">
            @foreach ($objects as $object)
                <tr>
                    <td class="px-3 py-2 font-display text-sm text-bno-active">{{ $object->number }}</td>
                    <td class="px-3 py-2 normal-case">{{ $object->slug }}</td>
                    <td class="px-3 py-2">{{ $object->status }}</td>
                    <td class="px-3 py-2">{{ $object->clearance_level }}</td>
                    <td class="px-3 py-2">
                        <a href="{{ route('registry.objects.edit', $object) }}" class="text-bno-muted hover:text-bno-active">Правка</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-xs text-bno-muted">
        {{ $objects->links() }}
    </div>
@endsection
