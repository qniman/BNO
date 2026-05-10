@extends('layouts.archive')

@section('title', 'Регистратура')

@section('content')
    <div class="border border-bno-border bg-black p-6">
        <p class="text-xs uppercase text-bno-muted">Скрытый интерфейс</p>
        <h1 class="font-display text-4xl uppercase tracking-[0.35em] text-bno-active">Узел регистратуры</h1>
        <p class="mt-4 max-w-2xl text-sm text-bno-archive doc-serif">
            Здесь выполняется первичный ввод объектов и правка карточек. Любые изменения фиксируются журналом аудита и могут потребовать очистки кэша каталога (выполняется автоматически).
        </p>

        <nav class="mt-6 flex flex-wrap gap-3 text-xs uppercase">
            <a href="{{ route('registry.objects.index') }}" class="border border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">Объекты</a>
            <a href="{{ route('registry.terminal.edit') }}" class="border border-dashed border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">Терминал</a>
            <a href="{{ route('registry.documents.index') }}" class="border border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">Документы</a>
            <a href="{{ route('registry.incidents.index') }}" class="border border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">Инциденты</a>
            <a href="{{ route('registry.objects.create') }}" class="border border-dashed border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">Новая запись</a>
            <a href="{{ route('registry.audit.index') }}" class="border border-bno-border px-4 py-2 text-bno-muted hover:border-bno-active hover:text-bno-active">Журнал аудита</a>
            <a href="{{ route('objects.index') }}" class="ml-auto border border-bno-border px-4 py-2 text-bno-muted hover:text-bno-active">← Публичный каталог</a>
        </nav>
    </div>
@endsection
