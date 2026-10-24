@extends('layouts.admin')

@section('title', 'История: '.$page->title)

@section('admin')
    <p class="text-[11px] uppercase text-bno-muted"><a href="{{ route('admin.pages.edit', $page) }}" class="hover:text-bno-active">← {{ $page->fullTitle() }}</a></p>
    <h1 class="mt-1 font-display text-3xl uppercase tracking-[0.2em] text-bno-active">История правок</h1>

    <ul class="mt-5 divide-y divide-bno-border border border-bno-border bg-bno-panel">
        @foreach ($revisions as $rev)
            <li class="flex flex-wrap items-center gap-3 px-4 py-3 text-sm">
                <span class="text-bno-archive">{{ $rev->created_at->format('d.m.Y H:i') }}</span>
                <span class="text-xs text-bno-muted">{{ $rev->user?->name ?? 'неизвестно' }}</span>
                @if ($rev->note)
                    <span class="font-serif italic text-bno-terminal">«{{ $rev->note }}»</span>
                @endif
                @if ($loop->first && $revisions->onFirstPage())
                    <span class="badge !border-bno-active/60 !text-bno-active">текущая</span>
                @endif
                <span class="ml-auto flex gap-2">
                    <a href="{{ route('admin.pages.revisions.show', [$page, $rev]) }}" class="btn !py-1">Просмотр</a>
                    @unless ($loop->first && $revisions->onFirstPage())
                        <form method="post" action="{{ route('admin.pages.revisions.restore', [$page, $rev]) }}" onsubmit="return confirm('Вернуть страницу к этой правке? Текущая версия останется в истории.')">
                            @csrf
                            <button class="btn !py-1">Восстановить</button>
                        </form>
                    @endunless
                </span>
            </li>
        @endforeach
    </ul>
    @include('partials.pager', ['paginator' => $revisions])
@endsection
