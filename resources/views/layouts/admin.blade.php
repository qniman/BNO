@extends('layouts.site')

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-2 border-b border-bno-border pb-3 text-xs uppercase">
        <span class="mr-2 font-display text-xl tracking-[0.25em] text-bno-alarm">Регистратура</span>
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
            <a href="{{ route($route) }}" class="border px-2 py-1 {{ request()->routeIs($pattern) ? 'border-bno-border text-bno-active' : 'border-transparent text-bno-muted hover:text-bno-active' }}">{{ $label }}</a>
        @endforeach
        <span class="ml-auto text-bno-muted">{{ auth()->user()->name }}</span>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button class="btn !py-1">Выйти</button>
        </form>
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
