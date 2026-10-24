@extends('layouts.admin')

@section('title', 'Типы страниц')

@section('admin')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="font-display text-3xl uppercase tracking-[0.2em] text-bno-active">Типы страниц</h1>
            <p class="mt-1 max-w-2xl text-xs text-bno-muted">Тип задаёт раздел сайта, набор полей и шаблон вывода. Новый вид материалов — это новый тип.</p>
        </div>
        <a href="{{ route('admin.types.create') }}" class="btn btn-primary">+ Новый тип</a>
    </div>

    <div class="mt-5 overflow-x-auto border border-bno-border bg-bno-panel">
        <table class="w-full text-sm">
            <thead class="border-b border-bno-border text-left text-[11px] uppercase text-bno-muted">
                <tr>
                    <th class="px-3 py-2 font-normal">Тип</th>
                    <th class="px-3 py-2 font-normal">Адрес</th>
                    <th class="px-3 py-2 font-normal">Шаблон</th>
                    <th class="px-3 py-2 font-normal">Поля</th>
                    <th class="px-3 py-2 font-normal">Страниц</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bno-border">
                @foreach ($types as $t)
                    <tr class="hover:bg-bno-card">
                        <td class="px-3 py-2">
                            <a href="{{ route('admin.types.edit', $t) }}" class="text-bno-archive hover:text-bno-active">{{ $t->name }}</a>
                            @unless ($t->show_in_nav)<span class="badge ml-1">скрыт из меню</span>@endunless
                        </td>
                        <td class="px-3 py-2 text-xs text-bno-muted">/{{ $t->url_prefix }}</td>
                        <td class="px-3 py-2 text-xs text-bno-muted">{{ \Illuminate\Support\Str::before(\App\Models\PageType::LAYOUTS[$t->layout] ?? $t->layout, ' (') }}</td>
                        <td class="px-3 py-2 text-xs text-bno-muted">{{ $t->fieldDefinitions()->pluck('label')->implode(', ') ?: '—' }}</td>
                        <td class="px-3 py-2 text-xs text-bno-muted">{{ $t->pages_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
