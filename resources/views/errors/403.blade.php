@extends('layouts.site')

@section('title', 'Доступ запрещён')

@section('content')
    <div class="mx-auto max-w-xl border border-bno-alarm/50 bg-bno-panel p-8 text-center">
        <p class="font-display text-6xl tracking-[0.2em] text-bno-alarm">403</p>
        <h1 class="mt-2 text-sm uppercase tracking-[0.2em] text-bno-archive">Недостаточный уровень допуска</h1>
        <a href="{{ route('home') }}" class="btn mt-6">На главную</a>
    </div>
@endsection
