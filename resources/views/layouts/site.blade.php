<!DOCTYPE html>
<html lang="ru" @if (str_contains((string) request()->userAgent(), 'BNOApp')) data-app @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#111111">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">
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
      x-data="{ crt: (() => { try { return localStorage.getItem('crt') !== 'off' } catch { return true } })(), menu: false, search: false }"
      x-init="$watch('crt', v => { try { localStorage.setItem('crt', v ? 'on' : 'off') } catch {} }); $watch('menu', v => document.documentElement.classList.toggle('overflow-hidden', v))"
      @keydown.escape.window="menu = false; search = false">
<div class="pointer-events-none fixed inset-0 z-40" x-show="crt" x-cloak>
    <div class="crt-overlay absolute inset-0 opacity-60" aria-hidden="true"></div>
    <div class="crt-vignette absolute inset-0" aria-hidden="true"></div>
</div>

<div class="relative z-10 flex min-h-screen flex-col">
    <header class="safe-top sticky top-0 z-30 border-b border-bno-border bg-bno-panel/95 backdrop-blur md:static md:bg-bno-panel">
        <div class="safe-x mx-auto flex max-w-6xl items-center gap-3 py-2.5 md:gap-4 md:py-3">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center border border-bno-border bg-black font-display text-xl tracking-widest text-bno-active md:h-11 md:w-11">БНО</span>
                <span class="min-w-0">
                    <span class="block truncate font-display text-base uppercase leading-none tracking-[0.2em] text-bno-active md:text-lg md:tracking-[0.3em]">
                        <span class="md:hidden">Архив Бюро</span><span class="hidden md:inline">Бюро нетипичных объектов</span>
                    </span>
                    <span class="mt-1 hidden text-[10px] uppercase text-bno-muted md:block">Открытый архив · рассекреченные материалы</span>
                </span>
            </a>

            <form action="{{ route('search') }}" method="get" class="ml-auto hidden md:block">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Поиск по архиву…"
                       class="w-56 border border-bno-border bg-black px-3 py-1.5 text-xs text-bno-archive outline-none focus:border-bno-active">
            </form>

            <div class="ml-auto flex items-center gap-2 text-[11px] uppercase md:ml-0">
                <button type="button" class="hidden border border-dashed border-bno-border px-2 py-1 text-bno-muted hover:text-bno-active md:block" @click="crt = !crt" title="Эффект ЭЛТ-монитора">
                    ЭЛТ: <span x-text="crt ? 'вкл' : 'выкл'">вкл</span>
                </button>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="hidden border border-bno-alarm/50 px-2 py-1 text-bno-alarm hover:bg-bno-card md:block">Регистратура</a>
                @endauth
                <button type="button" class="icon-btn md:hidden" @click="search = !search; menu = false; search && $nextTick(() => $refs.mobileSearch.focus())" aria-label="Поиск">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
                </button>
                <button type="button" class="icon-btn md:hidden" @click="menu = !menu; search = false" :aria-expanded="menu" aria-label="Меню">
                    <svg x-show="!menu" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="menu" x-cloak viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        {{-- Поиск на телефоне --}}
        <form x-show="search" x-cloak action="{{ route('search') }}" method="get" class="safe-x border-t border-bno-border py-2 md:hidden">
            <input x-ref="mobileSearch" type="search" name="q" value="{{ request('q') }}" placeholder="Номер, название, слово из текста…" enterkeyhint="search"
                   class="w-full border border-bno-border bg-black px-3 py-2 text-bno-archive outline-none focus:border-bno-active">
        </form>

        {{-- Разделы: строка на компьютере --}}
        <nav class="hidden border-t border-bno-border bg-black/40 md:block">
            <div class="mx-auto flex max-w-6xl flex-wrap gap-2 px-4 py-2 text-xs uppercase">
                @foreach ($navTypes as $navType)
                    @php($isActive = request()->route('prefix') === $navType->url_prefix)
                    <a href="{{ $navType->url() }}"
                       class="border px-2 py-1 hover:border-bno-border hover:text-bno-active {{ $isActive ? 'border-bno-border text-bno-active' : 'border-transparent text-bno-muted' }}">
                        {{ $navType->name_plural }}
                    </a>
                @endforeach
                <a href="{{ route('tags.index') }}"
                   class="border px-2 py-1 hover:border-bno-border hover:text-bno-active {{ request()->routeIs('tags.*') ? 'border-bno-border text-bno-active' : 'border-transparent text-bno-muted' }}">Теги</a>
            </div>
        </nav>

        {{-- Разделы: шторка на телефоне --}}
        <nav x-show="menu" x-cloak class="absolute inset-x-0 top-full max-h-[80vh] overflow-y-auto border-b border-bno-border bg-bno-panel md:hidden">
            <div class="divide-y divide-bno-border/60">
                @foreach ($navTypes as $navType)
                    @php($isActive = request()->route('prefix') === $navType->url_prefix)
                    <a href="{{ $navType->url() }}" class="flex items-center justify-between px-5 py-3.5 {{ $isActive ? 'text-bno-active' : 'text-bno-archive' }}">
                        <span class="font-display text-2xl uppercase tracking-[0.15em]">{{ $navType->name_plural }}</span>
                        <span class="text-bno-muted">→</span>
                    </a>
                @endforeach
                <a href="{{ route('tags.index') }}" class="flex items-center justify-between px-5 py-3.5 {{ request()->routeIs('tags.*') ? 'text-bno-active' : 'text-bno-archive' }}">
                    <span class="font-display text-2xl uppercase tracking-[0.15em]">Теги</span>
                    <span class="text-bno-muted">→</span>
                </a>
            </div>
            <div class="safe-x flex flex-wrap items-center gap-2 border-t border-bno-border py-3 text-[11px] uppercase">
                <button type="button" class="btn" @click="crt = !crt">ЭЛТ-эффект: <span x-text="crt ? 'вкл' : 'выкл'">вкл</span></button>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-danger">Регистратура</a>
                @else
                    <a href="{{ route('login') }}" class="btn">Вход для сотрудников</a>
                @endauth
            </div>
        </nav>
    </header>
    <div x-show="menu" x-cloak class="fixed inset-0 z-20 bg-black/70 md:hidden" @click="menu = false"></div>

    <main class="safe-x mx-auto w-full max-w-6xl flex-1 py-5 md:py-8">
        @if (session('status'))
            <p class="mb-6 border border-bno-active/50 bg-bno-active/10 px-3 py-2 text-xs uppercase text-bno-active">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>

    <footer class="safe-bottom border-t border-bno-border bg-bno-panel px-4 py-4 text-center text-[11px] uppercase text-bno-muted">
        <p>Бюро нетипичных объектов · Материалы являются художественным вымыслом</p>
        @guest
            <a href="{{ route('login') }}" class="mt-1 hidden opacity-50 hover:opacity-100 md:inline-block">Вход для сотрудников</a>
        @endguest
    </footer>
</div>
</body>
</html>
