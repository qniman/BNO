@extends('layouts.archive')

@section('title', 'Терминал доступа · Архив БНО')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        <section class="border border-bno-border bg-bno-panel p-6">
            <header class="mb-6 flex flex-col gap-2 border-b border-bno-border pb-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs uppercase text-bno-muted">Лента статуса</p>
                    <h1 class="font-display text-3xl uppercase tracking-[0.2em] text-bno-active">{{ $terminal['home.headline'] }}</h1>
                </div>
                <div class="flex gap-4 text-xs uppercase">
                    <div class="border border-bno-border bg-black px-3 py-2">
                        <p class="text-bno-muted">Уровень угрозы</p>
                        <p class="text-bno-archive">{{ $threat['label'] }} ({{ $threat['level'] }}/5)</p>
                    </div>
                    <div class="border border-bno-border bg-black px-3 py-2">
                        <p class="text-bno-muted">Состояние УЗАИС</p>
                        <p class="text-bno-active whitespace-pre-line">{{ $terminal['home.status_uzais'] }}</p>
                    </div>
                </div>
            </header>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="border border-dashed border-bno-border bg-bno-card p-4">
                    <p class="text-xs uppercase text-bno-muted">Предупреждение безопасности</p>
                    <p class="mt-3 text-sm leading-relaxed text-bno-archive doc-serif archive-md-flow">{!! \App\Services\ArchiveRedactor::rich($terminal['home.warning_intro']) !!}</p>
                    <ul class="mt-4 space-y-2 text-xs uppercase text-bno-terminal">
                        @foreach ($warningBullets as $bullet)
                            <li class="flex gap-2"><span class="text-bno-alarm">●</span> {{ $bullet }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="border border-bno-border bg-black p-4">
                    <p class="text-xs uppercase text-bno-muted">Авторизация</p>
                    @guest
                        <form method="post" action="{{ route('login') }}" class="mt-4 space-y-3">
                            @csrf
                            <label class="block text-[11px] uppercase text-bno-muted">
                                УЗ электропочты
                                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full border border-bno-border bg-bno-panel px-3 py-2 text-sm text-bno-archive outline-none focus:border-bno-active">
                            </label>
                            <label class="block text-[11px] uppercase text-bno-muted">
                                Пароль
                                <input type="password" name="password" required class="mt-1 w-full border border-bno-border bg-bno-panel px-3 py-2 text-sm text-bno-archive outline-none focus:border-bno-active">
                            </label>
                            <label class="flex items-center gap-2 text-[11px] uppercase text-bno-muted">
                                <input type="checkbox" name="remember" class="border border-bno-border bg-black accent-bno-active">
                                сохранить сеанс
                            </label>
                            @error('email')
                                <p class="text-xs uppercase text-bno-alarm">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="w-full border border-bno-active bg-bno-active px-3 py-2 text-xs uppercase text-black hover:bg-transparent hover:text-bno-active">
                                Установить соединение
                            </button>
                        </form>
                        <p class="mt-3 text-[11px] uppercase text-bno-muted">
                            Демо-доступ: director@bno.local · пароль <span class="text-bno-active">archive-demo</span>
                        </p>
                    @else
                        <div class="mt-4 border border-bno-border bg-bno-card p-4 text-sm text-bno-archive">
                            <p>
                                Сеанс: {{ auth()->user()->codename ?? auth()->user()->name }}
                                · эффективный допуск <span class="text-bno-active">{{ $effectiveClearance }}</span>
                                @if ($isDirector)
                                    <span class="text-[11px] uppercase text-bno-alarm">· режим директора</span>
                                @endif
                            </p>
                            <p class="mt-3 text-xs leading-relaxed doc-serif archive-md-flow">{!! \App\Services\ArchiveRedactor::rich($terminal['home.session_note']) !!}</p>
                            @can('manage-registry')
                                <p class="mt-4 text-[11px] uppercase text-bno-muted">
                                    <a href="{{ route('registry.terminal.edit') }}" class="border border-dashed border-bno-border px-2 py-1 hover:border-bno-active hover:text-bno-active">Редактор терминала</a>
                                    ·
                                    <a href="{{ route('registry.documents.index') }}" class="hover:text-bno-active">Документы</a>
                                </p>
                            @endcan
                        </div>
                    @endguest
                </div>
            </div>
        </section>

        <aside class="space-y-4">
            <div class="border border-bno-border bg-bno-panel p-4">
                <p class="text-xs uppercase text-bno-muted">Последние инциденты</p>
                <ul class="mt-3 space-y-3 text-xs">
                    @forelse ($incidents as $incident)
                        <li class="border border-bno-border bg-black px-3 py-2">
                            <p class="uppercase text-bno-active">{{ $incident->occurred_on?->format('d.m.Y') ?? '—' }}</p>
                            <p class="mt-1">
                                <a href="{{ route('incidents.show', $incident) }}" class="text-bno-archive hover:text-bno-active hover:underline">{{ \Illuminate\Support\Str::limit($incident->title, 120) }}</a>
                            </p>
                            <p class="mt-1 text-[10px] uppercase text-bno-muted">{{ $incident->severity }}</p>
                        </li>
                    @empty
                        <li class="text-bno-muted">Нет записей с вашим уровнем допуска.</li>
                    @endforelse
                </ul>
                <a href="{{ route('incidents.index') }}" class="mt-3 inline-block border border-dashed border-bno-border px-2 py-1 text-[11px] uppercase hover:border-bno-active hover:text-bno-active">Журнал инцидентов →</a>
            </div>

            <div class="border border-bno-border bg-bno-panel p-4">
                <p class="text-xs uppercase text-bno-muted">Системные сообщения</p>
                <ul class="mt-3 space-y-2 text-[11px] uppercase text-bno-terminal">
                    @foreach ($systemMessages as $message)
                        <li class="border border-bno-border bg-black px-2 py-1">{{ $message }}</li>
                    @endforeach
                </ul>
            </div>

            @if ($articles->isNotEmpty())
                <div class="border border-bno-border bg-bno-panel p-4">
                    <p class="text-xs uppercase text-bno-muted">Статьи и оперативные материалы</p>
                    <ul class="mt-3 space-y-3 text-xs">
                        @foreach ($articles as $article)
                            <li class="border border-bno-border bg-black px-3 py-2">
                                <a href="{{ route('documents.show', $article->slug) }}" class="font-display text-sm uppercase text-bno-active hover:underline">{{ $article->title }}</a>
                                <p class="mt-1 text-[10px] uppercase text-bno-muted">{{ $article->published_at?->format('d.m.Y') ?? '—' }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('documents.index', ['type' => 'статья']) }}" class="mt-3 inline-block border border-dashed border-bno-border px-2 py-1 text-[11px] uppercase hover:border-bno-active hover:text-bno-active">Все статьи →</a>
                </div>
            @endif
        </aside>
    </div>
@endsection
