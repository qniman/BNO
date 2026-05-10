@extends('layouts.archive')

@section('title', 'Секции БНО')

@section('content')
    <header class="mb-6 border-b border-bno-border pb-4">
        <p class="text-xs uppercase text-bno-muted">Оргструктура (справочно)</p>
        <h1 class="font-display text-4xl uppercase tracking-[0.35em] text-bno-active">Секции</h1>
    </header>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($divisions as $code => $title)
            <article class="border border-bno-border bg-black px-4 py-4">
                <p class="font-display text-5xl text-bno-active">{{ $code }}</p>
                <p class="mt-2 text-sm uppercase text-bno-muted">{{ $title }}</p>
                <p class="mt-3 text-xs leading-relaxed text-bno-archive doc-serif">
                    Доступ к материалам секции определяется отдельными регламентами. Данный блок не содержит оперативных сведений.
                </p>
            </article>
        @endforeach
    </div>
@endsection
