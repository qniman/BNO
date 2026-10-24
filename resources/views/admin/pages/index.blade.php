@extends('layouts.admin')

@section('title', 'Страницы')

@section('admin')
    <div class="flex flex-wrap items-end gap-3">
        <form method="get" class="flex flex-wrap items-end gap-3">
            <label>
                <span class="field-label">Тип</span>
                <select name="type" class="field-input !w-auto" onchange="this.form.submit()">
                    <option value="">Все</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->key }}" @selected($type?->id === $t->id)>{{ $t->name_plural }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="field-label">Статус</span>
                <select name="status" class="field-input !w-auto" onchange="this.form.submit()">
                    <option value="">Любой</option>
                    @foreach (\App\Models\Page::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="field-label">Название или адрес</span>
                <input type="search" name="q" value="{{ request('q') }}" class="field-input !w-56">
            </label>
            <button class="btn">Найти</button>
        </form>

        <div class="ml-auto flex flex-wrap gap-2" x-data="{ open: false }">
            <div class="relative">
                <button type="button" class="btn btn-primary" @click="open = !open" @click.outside="open = false">+ Создать</button>
                <div x-show="open" x-cloak class="absolute right-0 z-30 mt-1 w-56 border border-bno-border bg-bno-panel py-1">
                    @foreach ($types as $t)
                        <a href="{{ route('admin.pages.create', $t) }}" class="block px-3 py-1.5 text-sm hover:bg-bno-card hover:text-bno-active">{{ $t->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @include('admin.pages._table')
    @include('partials.pager', ['paginator' => $pages])
@endsection
