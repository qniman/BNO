@extends('layouts.site')

@section('title', $page->fullTitle())
@section('description', $page->summary)

@php
    $headerFields = $type->fieldDefinitions()
        ->where('header', true)
        ->map(fn ($f) => $f + ['html' => $presenter->html($f, $page->fieldValue($f['key']))])
        ->filter(fn ($f) => $f['html'] !== null)
        ->values();
@endphp

@section('content')
    <nav class="mb-4 flex flex-wrap items-center gap-2 text-[11px] uppercase text-bno-muted">
        <a href="{{ route('home') }}" class="hover:text-bno-active">Архив</a>
        <span>/</span>
        <a href="{{ $type->url() }}" class="hover:text-bno-active">{{ $type->name_plural }}</a>
        @auth
            <a href="{{ route('admin.pages.edit', $page) }}" class="btn ml-auto !py-1">Редактировать</a>
        @endauth
    </nav>

    @unless ($page->isPublished())
        <p class="mb-4 border border-bno-warn/60 bg-bno-warn/10 px-3 py-2 text-xs uppercase text-bno-warn">
            Черновик — страница видна только сотрудникам.
        </p>
    @endunless

    @include('pages.layouts.'.$type->layout, ['headerFields' => $headerFields])

    @if ($page->tags->isNotEmpty() || $backlinks->isNotEmpty())
        <aside class="mx-auto mt-10 max-w-4xl space-y-5 border-t border-bno-border pt-5">
            @if ($page->tags->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] uppercase text-bno-muted">Теги:</span>
                    @foreach ($page->tags as $tag)
                        <a href="{{ $tag->url() }}" class="badge hover:border-bno-active hover:text-bno-active">{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif
            @if ($backlinks->isNotEmpty())
                <div>
                    <p class="text-[11px] uppercase text-bno-muted">Упоминается в материалах</p>
                    <div class="mt-2 grid gap-4 sm:grid-cols-2">
                        @foreach ($backlinks as $group => $items)
                            <div>
                                <p class="text-xs uppercase text-bno-terminal">{{ $group }}</p>
                                <ul class="mt-1 space-y-1 text-sm">
                                    @foreach ($items as $item)
                                        <li><a href="{{ $item->url() }}" class="page-link">{{ $item->fullTitle() }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    @endif
@endsection
