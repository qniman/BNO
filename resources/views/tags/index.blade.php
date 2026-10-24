@extends('layouts.site')

@section('title', 'Теги')

@section('content')
    <h1 class="font-display text-4xl uppercase tracking-[0.2em] text-bno-active">Теги</h1>
    @if ($tags->isEmpty())
        <p class="mt-6 text-sm text-bno-muted">Тегов пока нет.</p>
    @else
        <div class="mt-6 flex flex-wrap gap-2">
            @foreach ($tags as $tag)
                <a href="{{ $tag->url() }}" class="border border-bno-border px-3 py-1.5 text-sm hover:border-bno-active hover:text-bno-active">
                    {{ $tag->name }} <span class="text-bno-muted">{{ $tag->pages_count }}</span>
                </a>
            @endforeach
        </div>
    @endif
@endsection
