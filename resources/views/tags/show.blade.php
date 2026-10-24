@extends('layouts.site')

@section('title', 'Тег «'.$tag->name.'»')

@section('content')
    <p class="text-[11px] uppercase text-bno-muted"><a href="{{ route('tags.index') }}" class="hover:text-bno-active">Теги</a> /</p>
    <h1 class="mt-1 font-display text-3xl md:text-4xl uppercase tracking-[0.2em] text-bno-active">{{ $tag->name }}</h1>

    @forelse ($pages as $group => $items)
        <section class="mt-6">
            <h2 class="text-xs uppercase text-bno-muted">{{ $group }}</h2>
            <ul class="mt-2 divide-y divide-bno-border border border-bno-border bg-bno-panel">
                @foreach ($items as $p)
                    <li>
                        <a href="{{ $p->url() }}" class="flex gap-3 px-4 py-2.5 hover:bg-bno-card">
                            @if ($p->code())
                                <span class="text-bno-active">{{ $p->code() }}</span>
                            @endif
                            <span class="font-serif text-bno-archive">{{ $p->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="mt-6 text-sm text-bno-muted">Материалов с этим тегом нет.</p>
    @endforelse
@endsection
