@extends('layouts.archive')

@section('title', 'Документы архива')

@section('content')
    <header class="mb-6 border-b border-bno-border pb-4">
        <p class="text-xs uppercase text-bno-muted">Раздел IV · документооборот</p>
        <h1 class="font-display text-4xl uppercase tracking-[0.35em] text-bno-active">Документы</h1>
    </header>

    <form method="get" class="mb-6 flex flex-wrap gap-3 border border-bno-border bg-bno-panel p-4 text-xs uppercase">
        <label class="flex flex-col text-bno-muted">
            тип
            <select name="type" class="mt-1 border border-bno-border bg-black px-2 py-2 text-bno-archive">
                <option value="">все</option>
                @foreach (config('bno.document_types') as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col text-bno-muted md:min-w-[260px]">
            строка поиска
            <input type="text" name="q" value="{{ request('q') }}" class="mt-1 border border-bno-border bg-black px-2 py-2 text-sm normal-case text-bno-archive">
        </label>
        <button type="submit" class="self-end border border-bno-active px-4 py-2 text-bno-active hover:bg-bno-active hover:text-black">искать</button>
    </form>

    <div class="space-y-3">
        @foreach ($documents as $document)
            <article class="border border-bno-border bg-black px-4 py-3 hover:bg-bno-card">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <a href="{{ route('documents.show', $document->slug) }}" class="font-display text-lg uppercase text-bno-active hover:underline">
                        {{ $document->title }}
                    </a>
                    <span class="text-[11px] uppercase text-bno-muted">{{ $document->type }} · допуск {{ $document->clearance_level }}</span>
                </div>
                @if ($document->classification)
                    <p class="mt-2 text-xs uppercase text-bno-muted">{{ $document->classification }}</p>
                @endif
            </article>
        @endforeach
    </div>

    <div class="mt-4 text-xs text-bno-muted">
        {{ $documents->links() }}
    </div>
@endsection
