@extends('layouts.archive')

@section('title', 'Инцидент #'.$incident->id)

@section('content')
    <article class="border border-bno-border bg-bno-panel p-6">
        <header class="border-b border-bno-border pb-4">
            <p class="text-xs uppercase text-bno-muted">Запись инцидента №{{ $incident->id }}</p>
            <h1 class="font-display text-3xl uppercase tracking-[0.2em] text-bno-active">{{ $incident->title }}</h1>
            <dl class="mt-4 grid gap-3 text-xs uppercase md:grid-cols-2">
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Дата</dt>
                    <dd class="mt-1 text-bno-archive">{{ $incident->occurred_on?->format('d.m.Y') ?? '—' }}</dd>
                </div>
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Уровень / последствия</dt>
                    <dd class="mt-1 text-bno-archive">{{ $incident->severity }} · {{ $incident->casualties ?? 'нет данных' }}</dd>
                </div>
                <div class="border border-bno-border bg-black px-3 py-2 md:col-span-2">
                    <dt class="text-bno-muted">Локация</dt>
                    <dd class="mt-1 text-bno-archive doc-serif normal-case">{{ $incident->location ?? '—' }}</dd>
                </div>
                @if ($incident->object)
                    <div class="border border-bno-border bg-black px-3 py-2 md:col-span-2">
                        <dt class="text-bno-muted">Связанный объект</dt>
                        <dd class="mt-1">
                            <a href="{{ route('objects.show', $incident->object->slug) }}" class="font-display text-lg text-bno-active hover:underline">{{ $incident->object->number }}</a>
                        </dd>
                    </div>
                @endif
            </dl>
        </header>

        <section class="mt-6">
            <h2 class="text-xs uppercase text-bno-muted">Описание</h2>
            <div class="archive-md-flow mt-3 text-sm leading-relaxed text-bno-archive doc-serif">{!! \App\Services\ArchiveRedactor::rich($incident->description ?? 'Описание отсутствует.') !!}</div>
        </section>

        <p class="mt-8 text-[11px] uppercase text-bno-muted">
            Допуск записи: {{ $incident->clearance_level }} · ваш эффективный уровень: {{ \App\Support\ArchiveAccess::clearance() }}
        </p>

        <a href="{{ route('incidents.index') }}" class="mt-4 inline-block border border-bno-border px-4 py-2 text-xs uppercase hover:border-bno-active hover:text-bno-active">← К хронологии</a>
    </article>
@endsection
