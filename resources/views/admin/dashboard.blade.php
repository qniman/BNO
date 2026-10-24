@extends('layouts.admin')

@section('title', 'Регистратура')

@section('admin')
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($types as $t)
            <div class="border border-bno-border bg-bno-panel p-4">
                <p class="font-display text-2xl uppercase tracking-[0.15em] text-bno-active">{{ $t->name_plural }}</p>
                <p class="mt-1 text-[11px] uppercase text-bno-muted">Всего {{ $t->pages_count }} · черновиков {{ $t->drafts_count }}</p>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.pages.create', $t) }}" class="btn btn-primary !py-1">+ {{ $t->name }}</a>
                    <a href="{{ route('admin.pages.index', ['type' => $t->key]) }}" class="btn !py-1">Список</a>
                </div>
            </div>
        @endforeach
        @can('manage-types')
            <a href="{{ route('admin.types.create') }}" class="flex items-center justify-center border border-dashed border-bno-border p-4 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">
                + Новый тип страниц
            </a>
        @endcan
    </section>

    <section class="mt-10">
        <h2 class="text-xs uppercase tracking-[0.2em] text-bno-muted">Недавно изменённые</h2>
        @include('admin.pages._table', ['pages' => $recent])
    </section>
@endsection
