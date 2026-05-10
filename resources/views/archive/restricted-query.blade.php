@extends('layouts.archive')

@section('title', 'Ответ узла поиска')

@section('content')
    <article class="border border-bno-border bg-bno-panel p-6">
        <p class="text-xs uppercase text-bno-muted">
            @if (($context ?? '') === 'objects')
                Реестр объектов
            @else
                Документный корпус
            @endif
        </p>
        <h1 class="font-display mt-2 text-3xl uppercase tracking-[0.35em] text-bno-active">Ответ узла</h1>
        <p class="mt-6 border border-dashed border-bno-border bg-black/40 px-4 py-3 font-mono text-sm leading-relaxed text-bno-archive doc-serif normal-case">
            {{ $message }}
        </p>
        <p class="mt-4 text-[11px] uppercase text-bno-muted">Запрос: <span class="font-mono normal-case text-bno-archive">{{ $query }}</span></p>
        <p class="mt-6 text-[11px] uppercase">
            <a href="{{ ($context ?? '') === 'objects' ? route('objects.index') : route('documents.index') }}" class="text-bno-active hover:underline">Вернуться к списку</a>
        </p>
    </article>
@endsection
