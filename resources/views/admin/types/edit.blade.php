@extends('layouts.admin')

@section('title', $type->exists ? 'Тип: '.$type->name : 'Новый тип')

@section('admin')
    <p class="text-[11px] uppercase text-bno-muted"><a href="{{ route('admin.types.index') }}" class="hover:text-bno-active">← Типы страниц</a></p>
    <h1 class="mt-1 font-display text-3xl uppercase tracking-[0.2em] text-bno-active">{{ $type->exists ? $type->name : 'Новый тип' }}</h1>

    <form method="post" action="{{ $type->exists ? route('admin.types.update', $type) : route('admin.types.store') }}"
          class="mt-5 space-y-6" x-data="fieldsEditor(@js(old('fields_json') ? json_decode(old('fields_json'), true) : ($type->fields ?? [])))">
        @csrf
        @if ($type->exists)
            @method('PUT')
        @endif

        <section class="grid gap-4 border border-bno-border bg-bno-panel p-4 md:grid-cols-3">
            <div>
                <label class="field-label" for="name">Название (ед. ч.)</label>
                <input id="name" name="name" value="{{ old('name', $type->name) }}" required placeholder="Объект" class="field-input">
            </div>
            <div>
                <label class="field-label" for="name_plural">Название раздела (мн. ч.)</label>
                <input id="name_plural" name="name_plural" value="{{ old('name_plural', $type->name_plural) }}" required placeholder="Объекты" class="field-input">
            </div>
            <div>
                <label class="field-label" for="layout">Шаблон вывода</label>
                <select id="layout" name="layout" class="field-input">
                    @foreach (\App\Models\PageType::LAYOUTS as $key => $label)
                        <option value="{{ $key }}" @selected(old('layout', $type->layout) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="field-label" for="key">Системный ключ</label>
                <input id="key" name="key" value="{{ old('key', $type->key) }}" required placeholder="object" class="field-input">
                <p class="field-help">Латиница, для автосписков и фильтров.</p>
            </div>
            <div>
                <label class="field-label" for="url_prefix">Адрес раздела</label>
                <div class="flex items-center gap-1 text-xs text-bno-muted">/<input id="url_prefix" name="url_prefix" value="{{ old('url_prefix', $type->url_prefix) }}" required placeholder="obj" class="field-input"></div>
            </div>
            <div>
                <label class="field-label" for="sort">Порядок в меню</label>
                <input id="sort" type="number" min="0" name="sort" value="{{ old('sort', $type->sort ?? 0) }}" class="field-input">
            </div>
            <div class="md:col-span-3">
                <label class="field-label" for="description">Описание раздела</label>
                <textarea id="description" name="description" rows="2" class="field-input">{{ old('description', $type->description) }}</textarea>
            </div>
            <div class="flex flex-wrap items-center gap-6 md:col-span-3" x-data="{ numbered: @js((bool) old('numbered', $type->numbered)) }">
                <label class="flex items-center gap-2 text-sm text-bno-archive">
                    <input type="hidden" name="show_in_nav" value="0">
                    <input type="checkbox" name="show_in_nav" value="1" @checked(old('show_in_nav', $type->show_in_nav)) class="accent-bno-active">
                    Показывать в меню
                </label>
                <label class="flex items-center gap-2 text-sm text-bno-archive">
                    <input type="hidden" name="numbered" value="0">
                    <input type="checkbox" name="numbered" value="1" x-model="numbered" class="accent-bno-active">
                    Нумерованные записи
                </label>
                <label x-show="numbered" class="flex items-center gap-2 text-sm text-bno-archive">
                    Формат номера
                    <input name="number_format" value="{{ old('number_format', $type->number_format ?? 'БНО-{n:3}') }}" class="field-input !w-40 !py-1">
                    <span class="text-[11px] text-bno-muted">{n:3} → 007</span>
                </label>
            </div>
        </section>

        <section class="border border-bno-border bg-bno-panel p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.15em] text-bno-active">Поля</p>
                    <p class="mt-1 text-xs text-bno-muted">Свои данные для каждой страницы этого типа: класс, дата, автор, связанные объекты…</p>
                </div>
                <button type="button" class="btn" @click="add()">+ Поле</button>
            </div>
            @error('fields_json')<p class="field-error">{{ $message }}</p>@enderror

            <input type="hidden" name="fields_json" :value="json">

            <template x-for="(field, i) in fields" :key="i">
                <div class="mt-4 grid gap-3 border border-bno-border bg-black/40 p-3 md:grid-cols-[1fr_1fr_1fr_auto]">
                    <div>
                        <span class="field-label">Название</span>
                        <input x-model="field.label" placeholder="Класс опасности" class="field-input !py-1">
                    </div>
                    <div>
                        <span class="field-label">Ключ</span>
                        <input x-model="field.key" placeholder="danger_class" class="field-input !py-1">
                    </div>
                    <div>
                        <span class="field-label">Тип значения</span>
                        <select x-model="field.type" class="field-input !py-1">
                            @foreach (\App\Models\PageType::FIELD_TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-1">
                        <button type="button" class="btn !px-2 !py-1" @click="move(i, -1)" title="Выше">↑</button>
                        <button type="button" class="btn !px-2 !py-1" @click="move(i, 1)" title="Ниже">↓</button>
                        <button type="button" class="btn btn-danger !px-2 !py-1" @click="remove(i)" title="Удалить поле">×</button>
                    </div>
                    <div x-show="field.type === 'select'" class="md:col-span-2">
                        <span class="field-label">Варианты (по одному в строке)</span>
                        <textarea x-model="field.options" rows="3" class="field-input !py-1"></textarea>
                    </div>
                    <div x-show="field.type === 'page' || field.type === 'pages'" class="md:col-span-2">
                        <span class="field-label">Только страницы типа</span>
                        <select x-model="field.page_type" class="field-input !py-1">
                            <option value="">Любого</option>
                            @foreach ($allTypes as $t)
                                <option value="{{ $t->key }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <span class="field-label">Подсказка</span>
                        <input x-model="field.help" class="field-input !py-1">
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-bno-archive md:col-span-4">
                        <label class="flex items-center gap-1.5"><input type="checkbox" x-model="field.required" class="accent-bno-active"> обязательное</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" x-model="field.header" class="accent-bno-active"> в шапке страницы</label>
                        <label class="flex items-center gap-1.5"><input type="checkbox" x-model="field.list" class="accent-bno-active"> столбец в списке раздела</label>
                    </div>
                </div>
            </template>
            <p x-show="!fields.length" class="mt-4 text-center text-xs text-bno-muted">Полей нет — у страниц будут только заголовок, описание и текст.</p>
        </section>

        <div class="flex flex-wrap gap-2">
            <button class="btn btn-primary">Сохранить</button>
            @if ($type->exists)
                <button type="submit" form="delete-type" class="btn btn-danger ml-auto">Удалить тип</button>
            @endif
        </div>
    </form>

    @if ($type->exists)
        <form id="delete-type" method="post" action="{{ route('admin.types.destroy', $type) }}" onsubmit="return confirm('Удалить тип?')">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
