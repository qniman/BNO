@extends('layouts.archive')

@section('title', 'Персонал')

@section('content')
    <header class="mb-6 border-b border-bno-border pb-4">
        <p class="text-xs uppercase text-bno-muted">Кадровый модуль</p>
        <h1 class="font-display text-4xl uppercase tracking-[0.35em] text-bno-active">Личные дела</h1>
    </header>

    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($staff as $person)
            <article class="border border-bno-border bg-black px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-display text-2xl uppercase text-bno-active">{{ $person->codename }}</p>
                        <p class="text-xs uppercase text-bno-muted">{{ $person->rank }}</p>
                    </div>
                    <span class="text-[11px] uppercase text-bno-muted">Секция {{ $person->division }} · Д{{ $person->clearance_level }}</span>
                </div>
                <p class="mt-3 text-xs uppercase {{ $person->deceased ? 'text-bno-alarm' : 'text-bno-terminal' }}">
                    {{ $person->status }}
                </p>
                <a href="{{ route('personnel.show', $person->codename) }}" class="mt-3 inline-block border border-dashed border-bno-border px-2 py-1 text-[11px] uppercase hover:border-bno-active hover:text-bno-active">Открыть профиль</a>
            </article>
        @endforeach
    </div>

    <div class="mt-4 text-xs text-bno-muted">
        {{ $staff->links() }}
    </div>
@endsection
