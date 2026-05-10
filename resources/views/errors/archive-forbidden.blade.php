@extends('layouts.archive')

@section('title', 'Недостаточный допуск')

@section('content')
    <article class="border border-bno-border bg-bno-panel p-6">
        <p class="text-xs uppercase text-bno-muted">УЗАИС · контроль доступа</p>
        <h1 class="font-display mt-2 text-3xl uppercase tracking-[0.35em] text-bno-alarm">Отказ</h1>
        <p class="mt-6 text-sm leading-relaxed text-bno-archive doc-serif normal-case">
            Недостаточный уровень допуска. Попытка зарегистрирована.
        </p>
        <p class="mt-6 text-[11px] uppercase">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('home') }}" class="text-bno-active hover:underline">Назад</a>
        </p>
    </article>
@endsection
