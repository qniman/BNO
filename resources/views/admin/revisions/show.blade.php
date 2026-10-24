@extends('layouts.admin')

@section('title', 'Правка от '.$revision->created_at->format('d.m.Y H:i'))

@section('admin')
    <p class="text-[11px] uppercase text-bno-muted"><a href="{{ route('admin.pages.revisions', $page) }}" class="hover:text-bno-active">← История правок</a></p>
    <div class="mt-2 flex flex-wrap items-center gap-3 border border-bno-warn/50 bg-bno-warn/10 px-4 py-2 text-xs uppercase text-bno-warn">
        Правка от {{ $revision->created_at->format('d.m.Y H:i') }} · {{ $revision->user?->name ?? 'неизвестно' }}
        @if ($revision->note) · «{{ $revision->note }}» @endif
        <form method="post" action="{{ route('admin.pages.revisions.restore', [$page, $revision]) }}" class="ml-auto">
            @csrf
            <button class="btn !py-1">Восстановить эту правку</button>
        </form>
    </div>

    <article class="mx-auto mt-8 max-w-3xl">
        <h1 class="font-serif text-3xl text-bno-archive">{{ $revision->title }}</h1>
        @if ($revision->summary)
            <p class="mt-2 font-serif italic text-bno-terminal">{{ $revision->summary }}</p>
        @endif
        @if (! empty($revision->tags))
            <p class="mt-2 text-xs text-bno-muted">Теги: {{ implode(', ', $revision->tags) }}</p>
        @endif
        <div class="prose-archive mt-6 flow-root">{!! $body !!}</div>
    </article>
@endsection
