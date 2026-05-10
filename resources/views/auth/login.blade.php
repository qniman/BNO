@extends('layouts.archive')

@section('title', 'Вход')

@section('content')
    <div class="mx-auto max-w-lg border border-bno-border bg-bno-panel p-6">
        <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">Аутентификация</h1>
        <p class="mt-2 text-xs uppercase text-bno-muted">Используйте учётные данные регистратуры.</p>

        <form method="post" action="{{ route('login') }}" class="mt-6 space-y-3">
            @csrf
            <label class="block text-[11px] uppercase text-bno-muted">
                УЗ электропочты
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="mt-1 w-full border border-bno-border bg-black px-3 py-2 text-sm text-bno-archive outline-none focus:border-bno-active">
            </label>
            <label class="block text-[11px] uppercase text-bno-muted">
                Пароль
                <input type="password" name="password" required autocomplete="current-password" class="mt-1 w-full border border-bno-border bg-black px-3 py-2 text-sm text-bno-archive outline-none focus:border-bno-active">
            </label>
            <label class="flex items-center gap-2 text-[11px] uppercase text-bno-muted">
                <input type="checkbox" name="remember" class="border border-bno-border bg-black accent-bno-active">
                сохранить сеанс
            </label>
            @error('email')
                <p class="text-xs uppercase text-bno-alarm">{{ $message }}</p>
            @enderror
            <button type="submit" class="w-full border border-bno-active bg-bno-active px-3 py-2 text-xs uppercase text-black hover:bg-transparent hover:text-bno-active">
                Войти
            </button>
        </form>
        <p class="mt-4 text-[11px] uppercase text-bno-muted">
            Демо: director@bno.local · archive-demo
        </p>
        <a href="{{ route('home') }}" class="mt-4 inline-block text-[11px] uppercase text-bno-active hover:underline">← На терминал</a>
    </div>
@endsection
