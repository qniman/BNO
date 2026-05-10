@extends('layouts.archive')

@section('title', 'Запись не найдена')

@section('content')
    <article class="border border-bno-border bg-bno-panel p-6">
        <p class="text-xs uppercase text-bno-muted">Архивный узел</p>
        <h1 class="font-display mt-2 text-3xl uppercase tracking-[0.35em] text-bno-active">Запись не найдена</h1>
        <p class="mt-6 text-sm leading-relaxed text-bno-archive doc-serif normal-case">
            Запись отсутствует в архиве или была изъята без замены ссылки.
        </p>
        <p class="mt-6 text-[11px] uppercase">
            <a href="{{ route('home') }}" class="text-bno-active hover:underline">На главную узла</a>
        </p>
    </article>
@endsection
