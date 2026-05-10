@extends('layouts.archive')

@section('title', 'Система доступа')

@section('content')
    <div class="border border-bno-border bg-black p-8">
        @if ($state === 0)
            <p class="font-display text-5xl uppercase tracking-[0.45em] text-bno-alarm">ACCESS DENIED</p>
            <p class="mt-6 text-xs uppercase text-bno-muted">Запрошенный узел заблокирован протоколом «ГРАНИЦА». Обратитесь в секцию АР при наличии ПИ‑формы.</p>
        @elseif ($state === 1)
            <p class="font-display text-4xl uppercase tracking-[0.35em] text-bno-active">Наблюдение зафиксировано</p>
            <p class="mt-6 max-w-3xl text-sm leading-relaxed text-bno-archive doc-serif">
                …он уже видел вас.
            </p>
            <p class="mt-4 text-[11px] uppercase text-bno-muted">Сессия будет завершена через неопределённый интервал.</p>
        @else
            <p class="font-display text-5xl uppercase tracking-[0.35em] text-bno-terminal">Ошибка 0xBNO‑ACCESS</p>
            <p class="mt-6 text-xs uppercase text-bno-muted">Подсистема выдачи ключей недоступна. Работа продолжается в режиме «ХОЛОДНЫЙ АРХИВ».</p>
        @endif

        <div class="mt-8 flex gap-4 text-xs uppercase">
            <a href="{{ route('home') }}" class="border border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">На терминал</a>
            <a href="{{ route('access.index') }}" class="border border-dashed border-bno-border px-4 py-2 hover:border-bno-active hover:text-bno-active">
                Повтор проверки
            </a>
        </div>
    </div>
@endsection
