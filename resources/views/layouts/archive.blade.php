<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Архив БНО')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ibm-plex-mono:400,500|vt323:400" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-bno-bg text-bno-terminal antialiased" x-data="{ crt: true }">
<div class="pointer-events-none fixed inset-0 z-40" x-show="crt" x-transition.opacity.duration.300ms>
    <div class="crt-overlay absolute inset-0 opacity-60" aria-hidden="true"></div>
    <div class="crt-vignette absolute inset-0" aria-hidden="true"></div>
</div>

<div id="archive-shell" class="relative z-10">
    <header class="border-b border-bno-border bg-bno-panel">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center border border-bno-border bg-black font-display text-xl tracking-widest text-bno-active">БНО</div>
                <div>
                    <p class="font-display text-lg uppercase tracking-[0.35em] text-bno-active">Бюро нетипичных объектов</p>
                    <p class="text-xs uppercase text-bno-muted">Внутренняя информационная система · закрытый контур</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs uppercase text-bno-muted">
                <span>Серверное время: <time datetime="{{ now()->toIso8601String() }}" class="text-bno-archive">{{ now()->timezone(config('app.timezone'))->format('d.m.Y H:i:s') }}</time></span>
                <button type="button" class="border border-dashed border-bno-border px-2 py-1 hover:border-bno-active hover:text-bno-active" @click="crt = !crt">CRT: <span x-text="crt ? 'ВКЛ' : 'ВЫКЛ'"></span></button>
                @auth
                    <span>{{ auth()->user()->codename ?? auth()->user()->name }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="border border-bno-border px-2 py-1 uppercase hover:bg-bno-card">Выход</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="border border-bno-border px-2 py-1 uppercase hover:bg-bno-card">Вход</a>
                @endauth
            </div>
        </div>
        @include('partials.nav')
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-bno-border bg-bno-panel py-4 px-4 text-center text-[11px] uppercase text-bno-muted">
        <p>Классификация: ВНУТРЕННЯЯ · Распространение запрещено · УМЗАИС v3.2 (совместимость NT)</p>
        @if (! empty($archiveWatchFooterHint))
            <p class="mt-2 max-w-3xl mx-auto text-[10px] leading-relaxed normal-case text-bno-muted doc-serif">{{ $archiveWatchFooterHint }}</p>
        @endif
    </footer>
</div>
</body>
</html>
