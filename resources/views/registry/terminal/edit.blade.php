@extends('layouts.archive')

@section('title', 'Редактор главной страницы')

@section('content')
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-bno-border pb-4">
        <div>
            <p class="text-xs uppercase text-bno-muted">Регистратура · директор</p>
            <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">Терминал (главная)</h1>
            <p class="mt-2 max-w-2xl text-xs uppercase text-bno-muted">
                Тексты главной страницы и боковой колонки. Статьи публикуются отдельно как документы с типом «статья».
            </p>
        </div>
        <a href="{{ route('registry.dashboard') }}" class="border border-bno-border px-4 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">Узел</a>
    </header>

    @if (session('status'))
        <div class="mb-4 border border-bno-border bg-bno-panel px-4 py-3 text-xs uppercase text-bno-active">{{ session('status') }}</div>
    @endif

    @error('bodies')
        <p class="mb-4 text-xs uppercase text-bno-alarm">{{ $message }}</p>
    @enderror

    <form method="post" action="{{ route('registry.terminal.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($entries as $entry)
            <div class="border border-bno-border bg-black p-4">
                <label class="block text-[11px] uppercase text-bno-muted">
                    {{ $entry['label'] }}
                    <span class="block text-[10px] normal-case text-bno-terminal">{{ $entry['key'] }}</span>
                </label>
                <input type="hidden" name="keys[]" value="{{ $entry['key'] }}">
                @if ($entry['multiline'] ?? false)
                    <x-registry.markdown-editor
                        name="bodies[]"
                        :value="$entry['value']"
                        placeholder="Текст блока (Markdown)…"
                        :min-height="$entry['key'] === 'home.warning_intro' ? 140 : 220"
                        class="mt-2"
                    />
                @else
                    <input type="text" name="bodies[]" value="{{ $entry['value'] }}" class="mt-2 w-full border border-bno-border bg-bno-panel px-3 py-2 text-sm normal-case text-bno-archive">
                @endif
            </div>
        @endforeach

        <button type="submit" class="border border-bno-active px-8 py-3 text-xs uppercase text-bno-active hover:bg-bno-active hover:text-black">
            Сохранить терминал
        </button>
    </form>
@endsection
