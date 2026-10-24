<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-mono:400,500,600|vt323:400|pt-serif:400,400i,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bno-bg text-bno-terminal antialiased"
      x-data="{ crt: (() => { try { return localStorage.getItem('crt') !== 'off' } catch { return true } })(), menu: false }"
      x-init="$watch('crt', v => { try { localStorage.setItem('crt', v ? 'on' : 'off') } catch {} })">
<div class="pointer-events-none fixed inset-0 z-40" x-show="crt" x-cloak>
    <div class="crt-overlay absolute inset-0 opacity-60" aria-hidden="true"></div>
    <div class="crt-vignette absolute inset-0" aria-hidden="true"></div>
</div>

<div class="relative z-10 flex min-h-screen flex-col">
    <header class="border-b border-bno-border bg-bno-panel">
        <div class="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center border border-bno-border bg-black font-display text-xl tracking-widest text-bno-active">БНО</span>
                <span class="hidden sm:block">
                    <span class="block font-display text-lg uppercase leading-none tracking-[0.3em] text-bno-active">Бюро нетипичных объектов</span>
                    <span class="mt-1 block text-[10px] uppercase text-bno-muted">Открытый архив · рассекреченные материалы</span>
                </span>
            </a>

            <form action="{{ route('search') }}" method="get" class="ml-auto hidden md:block">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск по архиву…"
                       class="w-56 border border-bno-border bg-black px-3 py-1.5 text-xs text-bno-archive outline-none focus:border-bno-active">
            </form>

            <div class="ml-auto flex items-center gap-2 text-[11px] uppercase md:ml-0">
                <button type="button" class="hidden border border-dashed border-bno-border px-2 py-1 text-bno-muted hover:text-bno-active sm:block" @click="crt = !crt" title="Эффект ЭЛТ-монитора">
                    ЭЛТ: <span x-text="crt ? 'вкл' : 'выкл'">вкл</span>
                </button>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="border border-bno-alarm/50 px-2 py-1 text-bno-alarm hover:bg-bno-card">Регистратура</a>
                @endauth
                <button type="button" class="border border-bno-border px-2 py-1 md:hidden" @click="menu = !menu">Меню</button>
            </div>
        </div>

        <nav class="border-t border-bno-border bg-black/40" :class="menu ? 'block' : 'hidden md:block'">
            <div class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-2 text-xs uppercase md:flex-row md:flex-wrap md:gap-2">
                @foreach ($navTypes as $navType)
                    @php($isActive = request()->route('prefix') === $navType->url_prefix)
                    <a href="{{ $navType->url() }}"
                       class="border px-2 py-1 hover:border-bno-border hover:text-bno-active {{ $isActive ? 'border-bno-border text-bno-active' : 'border-transparent text-bno-muted' }}">
                        {{ $navType->name_plural }}
                    </a>
                @endforeach
                <a href="{{ route('tags.index') }}"
                   class="border px-2 py-1 hover:border-bno-border hover:text-bno-active {{ request()->routeIs('tags.*') ? 'border-bno-border text-bno-active' : 'border-transparent text-bno-muted' }}">Теги</a>
                <form action="{{ route('search') }}" method="get" class="md:hidden">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск по архиву…"
                           class="mt-1 w-full border border-bno-border bg-black px-3 py-1.5 text-xs text-bno-archive outline-none">
                </form>
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        @if (session('status'))
            <p class="mb-6 border border-bno-active/50 bg-bno-active/10 px-3 py-2 text-xs uppercase text-bno-active">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-bno-border bg-bno-panel px-4 py-4 text-center text-[11px] uppercase text-bno-muted">
        <p>Бюро нетипичных объектов · Материалы являются художественным вымыслом</p>
        @guest
            <a href="{{ route('login') }}" class="mt-1 inline-block opacity-50 hover:opacity-100">Вход для сотрудников</a>
        @endguest
    </footer>
</div>
</body>
</html>
