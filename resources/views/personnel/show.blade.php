@extends('layouts.archive')

@section('title', $person->codename)

@section('content')
    <article class="border border-bno-border bg-bno-panel p-6">
        <header class="border-b border-bno-border pb-4">
            <p class="text-xs uppercase text-bno-muted">Уровень допуска {{ $person->clearance_level }}</p>
            <h1 class="font-display text-4xl uppercase tracking-[0.25em] text-bno-active">{{ $person->codename }}</h1>
            <dl class="mt-4 grid gap-3 text-xs uppercase md:grid-cols-2">
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Шифр ФИО</dt>
                    <dd class="mt-1 text-bno-archive doc-serif normal-case">{{ $person->real_name ?? '[[снято]]' }}</dd>
                </div>
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Звание / должность</dt>
                    <dd class="mt-1 text-bno-archive">{{ $person->rank }}</dd>
                </div>
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Секция</dt>
                    <dd class="mt-1 text-bno-active">{{ $person->division }}</dd>
                </div>
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Психотип</dt>
                    <dd class="mt-1 text-bno-archive">{{ $person->profile_type }}</dd>
                </div>
            </dl>
        </header>

        <section class="mt-6">
            <h2 class="text-xs uppercase text-bno-muted">Статус</h2>
            <p class="mt-2 text-sm uppercase {{ $person->deceased ? 'text-bno-alarm' : 'text-bno-terminal' }}">{{ $person->status }}</p>
            <div class="archive-md-flow mt-4 text-sm leading-relaxed text-bno-archive doc-serif">{!! \App\Services\ArchiveRedactor::rich($person->biography ?? 'Биография недоступна.') !!}</div>
        </section>
    </article>
@endsection
