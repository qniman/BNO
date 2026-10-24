@extends('layouts.site')

@section('title', 'Поиск')

@section('content')
    <h1 class="font-display text-3xl md:text-4xl uppercase tracking-[0.2em] text-bno-active">Поиск по архиву</h1>
    <form action="{{ route('search') }}" method="get" class="mt-4 flex max-w-2xl gap-2">
        <input type="search" name="q" value="{{ $q }}" autofocus placeholder="Номер, название, слово из текста…" class="field-input">
        <button class="btn btn-primary shrink-0">Найти</button>
    </form>

    @if (mb_strlen($q) >= 2)
        <p class="mt-6 text-[11px] uppercase text-bno-muted">Найдено: {{ $pages->count() }}</p>
        <ul class="mt-3 space-y-3">
            @foreach ($pages as $p)
                <li class="border border-bno-border bg-bno-panel px-4 py-3">
                    <a href="{{ $p->url() }}" class="flex flex-wrap items-baseline gap-2">
                        <span class="badge">{{ $p->type->name }}</span>
                        @if ($p->code())
                            <span class="text-bno-active">{{ $p->code() }}</span>
                        @endif
                        <span class="font-serif text-lg text-bno-archive hover:text-bno-active">{{ $p->title }}</span>
                    </a>
                    <p class="mt-1 font-serif text-sm text-bno-terminal">{{ $snippet($p) }}</p>
                </li>
            @endforeach
        </ul>
    @elseif ($q !== '')
        <p class="mt-6 text-sm text-bno-muted">Введите хотя бы два символа.</p>
    @endif
@endsection
