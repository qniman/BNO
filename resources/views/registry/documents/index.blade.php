@extends('layouts.archive')

@section('title', 'Регистратура · документы')

@section('content')
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-bno-border pb-4">
        <div>
            <p class="text-xs uppercase text-bno-muted">Регистратура</p>
            <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">Документы</h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('registry.documents.create') }}" class="border border-bno-active px-4 py-2 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">Новый документ</a>
            <a href="{{ route('registry.dashboard') }}" class="border border-bno-border px-4 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Узел</a>
        </div>
    </header>

    @if (session('status'))
        <div class="mb-4 border border-bno-border bg-bno-panel px-4 py-3 text-xs uppercase text-bno-active">{{ session('status') }}</div>
    @endif

    <div class="space-y-3">
        @foreach ($documents as $doc)
            <article class="flex flex-wrap items-start justify-between gap-4 border border-bno-border bg-black px-4 py-3">
                <div>
                    <a href="{{ route('documents.show', $doc->slug) }}" class="font-display text-lg uppercase text-bno-active hover:underline">{{ $doc->title }}</a>
                    <p class="mt-1 text-[11px] uppercase text-bno-muted">{{ $doc->type }} · slug {{ $doc->slug }} · Д{{ $doc->clearance_level }}</p>
                </div>
                <div class="flex flex-wrap gap-2 text-[11px] uppercase">
                    <a href="{{ route('registry.documents.edit', $doc) }}" class="border border-bno-border px-3 py-1 hover:border-bno-active hover:text-bno-active">Правка</a>
                    <form method="post" action="{{ route('registry.documents.destroy', $doc) }}" onsubmit="return confirm('Удалить документ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="border border-bno-alarm px-3 py-1 text-bno-alarm hover:bg-bno-alarm hover:text-black">Удалить</button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-4 text-xs text-bno-muted">{{ $documents->links() }}</div>
@endsection
