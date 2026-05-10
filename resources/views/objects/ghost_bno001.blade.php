@extends('layouts.archive')

@section('title', 'Запись удалена')

@section('content')
    <div class="border border-bno-alarm bg-black p-8 {{ $ghost['mode'] === 'static' ? 'animate-pulse' : '' }}">
        @if ($ghost['mode'] === 'deleted')
            <p class="font-display text-5xl uppercase tracking-[0.4em] text-bno-alarm">Запись удалена.</p>
            <p class="mt-6 max-w-2xl text-sm uppercase text-bno-muted">
                Идентификатор БНО-001 исключён из активного регистра приказом СУМ‑91. Любые совпадения с действующими объектами считаются ошибкой индексации.
            </p>
        @elseif ($ghost['mode'] === 'leak')
            <p class="font-display text-4xl uppercase tracking-[0.35em] text-bno-active">ДОСТУП ЗАБЛОКИРОВАН</p>
            <p class="mt-6 max-w-3xl border border-dashed border-bno-border bg-bno-panel p-4 text-sm leading-relaxed text-bno-archive doc-serif">
                {{ $ghost['fragment'] }}
            </p>
            <p class="mt-4 text-xs uppercase text-bno-alarm">Попытка занесена в журнал нештатных совпадений.</p>
        @else
            <p class="font-display text-5xl uppercase tracking-[0.4em] text-bno-terminal">СИГНАЛ ПОМЕХ</p>
            <p class="mt-6 text-xs uppercase text-bno-muted">
                Узел Архива не может восстановить контрольную сумму страницы. Повторите запрос через 15 минут или покиньте терминал.
            </p>
        @endif

        <div class="mt-8 flex gap-4 text-xs uppercase">
            <a href="{{ route('objects.index') }}" class="border border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">← К каталогу</a>
            <button type="button" class="border border-dashed border-bno-border px-4 py-2 text-bno-muted" onclick="window.location.reload()">Повтор запроса</button>
        </div>
    </div>
@endsection
