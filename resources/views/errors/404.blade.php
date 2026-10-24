@extends('layouts.site')

@section('title', 'Запись не найдена')

@section('content')
    <div class="mx-auto max-w-xl border border-bno-border bg-bno-panel p-8 text-center">
        <p class="font-display text-6xl tracking-[0.2em] text-bno-alarm">404</p>
        <h1 class="mt-2 text-sm uppercase tracking-[0.2em] text-bno-archive">Запись отсутствует в архиве</h1>
        <p class="mt-3 font-serif text-bno-terminal">Документ изъят, перемещён или никогда не существовал. Если вы уверены в обратном — не сообщайте об этом никому.</p>
        <a href="{{ route('home') }}" class="btn mt-6">На главную</a>
    </div>
@endsection
