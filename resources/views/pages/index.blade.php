@extends('layouts.site')

@section('title', $type->name_plural)
@section('description', $type->description)

@php
    $listFields = $type->fieldDefinitions()->where('list', true);
    $selectFields = $type->fieldDefinitions()->where('type', 'select');
@endphp

@section('content')
    <header class="border-b border-bno-border pb-5">
        <p class="text-[11px] uppercase text-bno-muted">Раздел архива</p>
        <h1 class="mt-1 font-display text-3xl md:text-4xl uppercase tracking-[0.2em] text-bno-active">{{ $type->name_plural }}</h1>
        @if ($type->description)
            <p class="mt-3 max-w-3xl font-serif leading-relaxed text-bno-archive">{{ $type->description }}</p>
        @endif
    </header>

    <form method="get" class="mt-5 grid grid-cols-2 items-end gap-3 text-xs sm:flex sm:flex-wrap">
        @foreach ($selectFields as $field)
            <label>
                <span class="field-label">{{ $field['label'] }}</span>
                <select name="f[{{ $field['key'] }}]" class="field-input !py-1 sm:!w-auto" onchange="this.form.submit()">
                    <option value="">Все</option>
                    @foreach ($field['options'] ?? [] as $option)
                        <option value="{{ $option }}" @selected(($filters[$field['key']] ?? null) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
        @endforeach
        <label>
            <span class="field-label">Порядок</span>
            <select name="sort" class="field-input !py-1 sm:!w-auto" onchange="this.form.submit()">
                @if ($type->numbered)
                    <option value="number" @selected($sort === 'number')>По номеру</option>
                @endif
                <option value="title" @selected($sort === 'title')>По названию</option>
                <option value="recent" @selected($sort === 'recent')>Новые сверху</option>
            </select>
        </label>
        @if ($tag)
            <input type="hidden" name="tag" value="{{ $tag->slug }}">
            <a href="{{ request()->fullUrlWithQuery(['tag' => null]) }}" class="btn !py-1">Тег: {{ $tag->name }} ×</a>
        @endif
        <noscript><button class="btn !py-1">Применить</button></noscript>
    </form>

    @if ($pages->isEmpty())
        <p class="mt-8 border border-dashed border-bno-border p-6 text-center text-sm text-bno-muted">Записей не найдено.</p>
    @else
        {{-- Телефон: карточки --}}
        <ul class="mt-5 space-y-2 md:hidden">
            @foreach ($pages as $p)
                <li>
                    <a href="{{ $p->url() }}" class="block border border-bno-border bg-bno-panel px-4 py-3 active:bg-bno-card">
                        @if ($p->code())
                            <span class="font-display text-xl tracking-[0.1em] text-bno-active">{{ $p->code() }}</span>
                        @endif
                        <span class="block font-serif text-lg leading-snug text-bno-archive">{{ $p->title }}</span>
                        @if ($p->summary)
                            <span class="mt-1 block font-serif text-sm text-bno-terminal">{{ \Illuminate\Support\Str::limit($p->summary, 140) }}</span>
                        @endif
                        @php($listValues = $listFields->map(fn ($f) => [$f['label'], $presenter->html($f, $p->fieldValue($f['key']))])->filter(fn ($v) => $v[1] !== null))
                        @if ($listValues->isNotEmpty())
                            <span class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($listValues as [$label, $html])
                                    <span class="badge" title="{{ $label }}">{!! strip_tags($html) !!}</span>
                                @endforeach
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Компьютер: таблица --}}
        <div class="mt-5 hidden overflow-x-auto border border-bno-border bg-bno-panel md:block">
            <table class="w-full text-sm">
                <thead class="border-b border-bno-border text-left text-[11px] uppercase text-bno-muted">
                    <tr>
                        @if ($type->numbered)
                            <th class="px-4 py-2 font-normal">Номер</th>
                        @endif
                        <th class="px-4 py-2 font-normal">Название</th>
                        @foreach ($listFields as $field)
                            <th class="px-4 py-2 font-normal">{{ $field['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-bno-border">
                    @foreach ($pages as $p)
                        <tr class="hover:bg-bno-card">
                            @if ($type->numbered)
                                <td class="whitespace-nowrap px-4 py-2.5 align-top">
                                    <a href="{{ $p->url() }}" class="text-bno-active hover:underline">{{ $p->code() }}</a>
                                </td>
                            @endif
                            <td class="px-4 py-2.5 align-top">
                                <a href="{{ $p->url() }}" class="font-serif text-base text-bno-archive hover:text-bno-active">{{ $p->title }}</a>
                                @if ($p->summary)
                                    <p class="mt-0.5 text-xs text-bno-muted">{{ \Illuminate\Support\Str::limit($p->summary, 160) }}</p>
                                @endif
                            </td>
                            @foreach ($listFields as $field)
                                <td class="px-4 py-2.5 align-top text-xs text-bno-terminal">{!! $presenter->html($field, $p->fieldValue($field['key'])) ?? '—' !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['paginator' => $pages])
    @endif
@endsection
