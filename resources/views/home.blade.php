@extends('layouts.site')

@section('content')
    <section class="border border-bno-border bg-bno-panel p-5 md:p-8">
        <p class="text-[11px] uppercase text-bno-muted">Терминал открытого доступа</p>
        <h1 class="mt-2 font-display text-3xl uppercase tracking-[0.2em] text-bno-active md:text-5xl">Архив Бюро</h1>
        <p class="mt-4 max-w-3xl font-serif text-lg leading-relaxed text-bno-archive">
            Бюро нетипичных объектов выявляет, изучает и удерживает предметы, места и явления, которые не укладываются в известную картину мира.
            Здесь собраны рассекреченные материалы: описания объектов, служебные документы и свидетельства.
        </p>
        <form action="{{ route('search') }}" method="get" class="mt-6 flex max-w-xl gap-2">
            <input type="search" name="q" placeholder="Номер объекта, название, ключевое слово…" class="field-input">
            <button class="btn btn-primary shrink-0">Найти</button>
        </form>
    </section>

    @if ($types->isNotEmpty())
        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($types as $t)
                <a href="{{ $t->url() }}" class="group block border border-bno-border bg-bno-panel p-4 hover:border-bno-active">
                    <p class="font-display text-2xl uppercase tracking-[0.15em] text-bno-active">{{ $t->name_plural }}</p>
                    <p class="mt-1 text-[11px] uppercase text-bno-muted">Записей: {{ $t->pages_count }}</p>
                    @if ($t->description)
                        <p class="mt-3 text-xs leading-relaxed text-bno-terminal">{{ \Illuminate\Support\Str::limit($t->description, 140) }}</p>
                    @endif
                </a>
            @endforeach
        </section>
    @endif

    <section class="mt-10">
        <h2 class="text-xs uppercase tracking-[0.2em] text-bno-muted">Последние поступления</h2>
        @forelse ($recent as $p)
            @if ($loop->first)<ul class="mt-3 divide-y divide-bno-border border border-bno-border bg-bno-panel">@endif
            <li>
                <a href="{{ $p->url() }}" class="flex flex-wrap items-baseline gap-x-3 gap-y-1 px-4 py-3 hover:bg-bno-card">
                    <span class="badge">{{ $p->type->name }}</span>
                    @if ($p->code())
                        <span class="text-sm text-bno-active">{{ $p->code() }}</span>
                    @endif
                    <span class="font-serif text-bno-archive">{{ $p->title }}</span>
                    <span class="ml-auto text-[11px] text-bno-muted">{{ $p->published_at?->format('d.m.Y') }}</span>
                </a>
            </li>
            @if ($loop->last)</ul>@endif
        @empty
            <p class="mt-3 text-sm text-bno-muted">Архив пока пуст.</p>
        @endforelse
    </section>
@endsection
