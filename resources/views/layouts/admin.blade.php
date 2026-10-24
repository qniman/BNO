@extends('layouts.site')

@section('content')
    <div class="mb-5 border-b border-bno-border pb-3 text-xs uppercase md:mb-6">
    <div class="flex items-center gap-2">
        <span class="mr-2 font-display text-xl tracking-[0.25em] text-bno-alarm">Регистратура</span>
        <span class="ml-auto hidden text-bno-muted sm:inline">{{ auth()->user()->name }}</span>
        <form method="post" action="{{ route('logout') }}" class="ml-auto sm:ml-0">
            @csrf
            <button class="btn !py-1">Выйти</button>
        </form>
    </div>
    <nav class="-mx-1 mt-2 flex gap-1 overflow-x-auto">
        @php
            $links = [
                ['admin.dashboard', 'Обзор', 'admin.dashboard'],
                ['admin.pages.index', 'Страницы', 'admin.pages.*'],
            ];
            if (auth()->user()->can('manage-types')) {
                $links[] = ['admin.types.index', 'Типы страниц', 'admin.types.*'];
            }
        @endphp
        @foreach ($links as [$route, $label, $pattern])
            <a href="{{ route($route) }}" class="shrink-0 border px-3 py-2 md:px-2 md:py-1 {{ request()->routeIs($pattern) ? 'border-bno-border text-bno-active' : 'border-transparent text-bno-muted hover:text-bno-active' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('home') }}" class="ml-auto shrink-0 border border-transparent px-3 py-2 text-bno-muted hover:text-bno-active md:px-2 md:py-1">На сайт ↗</a>
    </nav>
    </div>

    @if ($errors->any() && ! isset($inlineErrors))
        <div class="mb-6 border border-bno-alarm/60 bg-bno-alarm/10 px-3 py-2 text-xs text-bno-alarm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @yield('admin')
@endsection
