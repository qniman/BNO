@extends('layouts.site')

@section('title', 'Вход')

@section('content')
    <div class="mx-auto max-w-md border border-bno-border bg-bno-panel p-6">
        <h1 class="font-display text-3xl uppercase tracking-[0.3em] text-bno-active">Регистратура</h1>
        <p class="mt-1 text-[11px] uppercase text-bno-muted">Вход для сотрудников архива</p>

        <form method="post" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf
            <label class="block">
                <span class="field-label">Почта</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field-input">
            </label>
            <label class="block">
                <span class="field-label">Пароль</span>
                <input type="password" name="password" required autocomplete="current-password" class="field-input">
            </label>
            <label class="flex items-center gap-2 text-[11px] uppercase text-bno-muted">
                <input type="checkbox" name="remember" class="accent-bno-active"> запомнить
            </label>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn btn-primary w-full justify-center">Войти</button>
        </form>
    </div>
@endsection
