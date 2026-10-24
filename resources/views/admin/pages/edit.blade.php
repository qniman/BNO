@extends('layouts.admin', ['inlineErrors' => true])

@section('title', $page->exists ? 'Правка: '.$page->title : 'Новая страница: '.$type->name)

@php
    $editorTypes = \App\Models\PageType::orderBy('sort')->get(['key', 'name'])->map->only(['key', 'name'])->values();
    $tool = fn (string $key) => "'ed-tool' + (active.$key ? ' on' : '')";
@endphp

@section('admin')
    <form method="post"
          action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store', $type) }}"
          class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]"
          x-data="{ dirty: false }" @input="dirty = true" @submit="dirty = false"
          x-init="window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault() } })">
        @csrf
        @if ($page->exists)
            @method('PUT')
        @endif

        {{-- Основная колонка: заголовок и текст --}}
        <div class="min-w-0">
            <p class="text-[11px] uppercase text-bno-muted">
                {{ $type->name }}@if ($page->exists && $page->code()) · {{ $page->code() }}@endif
                @if ($page->exists)
                    · <a href="{{ $page->url() }}" target="_blank" class="hover:text-bno-active">открыть на сайте ↗</a>
                @endif
            </p>
            <input name="title" value="{{ old('title', $page->title) }}" required placeholder="Название"
                   class="mt-1 w-full border-0 border-b border-bno-border bg-transparent py-2 font-serif text-3xl text-bno-archive outline-none focus:border-bno-active">
            @error('title')<p class="field-error">{{ $message }}</p>@enderror

            <div class="mt-5" data-block-editor
                 x-data="blockEditor({ lookupUrl: @js(route('admin.lookup.pages')), uploadUrl: @js(route('admin.media.store')), types: @js($editorTypes) })"
                 @keydown.escape.window="picker.open && closePicker()">
                <div class="ed-toolbar">
                    <button type="button" :class="{{ $tool('h2') }}" @mousedown.prevent @click="run('h2')" title="Заголовок">H2</button>
                    <button type="button" :class="{{ $tool('h3') }}" @mousedown.prevent @click="run('h3')" title="Подзаголовок">H3</button>
                    <button type="button" :class="{{ $tool('h4') }}" @mousedown.prevent @click="run('h4')" title="Малый заголовок">H4</button>
                    <span class="ed-sep"></span>
                    <button type="button" :class="{{ $tool('bold') }}" @mousedown.prevent @click="run('toggleBold')" title="Жирный (Ctrl+B)"><b>Ж</b></button>
                    <button type="button" :class="{{ $tool('italic') }}" @mousedown.prevent @click="run('toggleItalic')" title="Курсив (Ctrl+I)"><i>К</i></button>
                    <button type="button" :class="{{ $tool('underline') }}" @mousedown.prevent @click="run('toggleUnderline')" title="Подчёркнутый (Ctrl+U)"><u>Ч</u></button>
                    <button type="button" :class="{{ $tool('strike') }}" @mousedown.prevent @click="run('toggleStrike')" title="Зачёркнутый"><s>З</s></button>
                    <button type="button" :class="{{ $tool('redacted') }}" @mousedown.prevent @click="run('toggleRedacted')" title="Цензура █████ (Ctrl+Shift+H)">███</button>
                    <span class="ed-sep"></span>
                    <button type="button" :class="{{ $tool('pageLink') }}" @mousedown.prevent @click="linkPage()" title="Ссылка на страницу архива">⧉ Стр.</button>
                    <button type="button" :class="{{ $tool('link') }}" @mousedown.prevent @click="run('link')" title="Внешняя ссылка">🔗</button>
                    <span class="ed-sep"></span>
                    <button type="button" :class="{{ $tool('bulletList') }}" @mousedown.prevent @click="run('toggleBulletList')" title="Список">•</button>
                    <button type="button" :class="{{ $tool('orderedList') }}" @mousedown.prevent @click="run('toggleOrderedList')" title="Нумерованный список">1.</button>
                    <button type="button" :class="{{ $tool('blockquote') }}" @mousedown.prevent @click="run('toggleBlockquote')" title="Цитата">❝</button>
                    <span class="ed-sep"></span>
                    <div class="relative">
                        <button type="button" class="ed-tool !text-bno-active" @mousedown.prevent @click="insertOpen = !insertOpen" @click.outside="insertOpen = false">+ Блок</button>
                        <div x-show="insertOpen" x-cloak class="absolute left-0 z-30 mt-1 w-64 border border-bno-border bg-bno-panel py-1 text-xs">
                            @foreach ([
                                ['collapsible', 'Раскрывающийся блок', 'приложения, протоколы'],
                                ['restricted', 'Ограниченный доступ', 'скрыт до «проверки допуска»'],
                                ['docBlock', 'Документ-вставка', 'бланк: рапорт, протокол, записка'],
                                ['callout', 'Врезка', 'справка или предупреждение'],
                                ['image', 'Изображение', 'загрузить файл'],
                                ['table', 'Таблица', '3×3 с заголовком'],
                                ['pageList', 'Автосписок страниц', 'по типу и тегу'],
                                ['pageCard', 'Карточка страницы', 'ссылка-плашка'],
                                ['footnote', 'Сноска', 'примечание внизу'],
                                ['hr', 'Разделитель', ''],
                            ] as [$block, $label, $hint])
                                <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-bno-card" @mousedown.prevent @click="insert('{{ $block }}')">
                                    <span class="text-bno-archive">{{ $label }}</span>
                                    @if ($hint)<span class="block text-[10px] text-bno-muted">{{ $hint }}</span>@endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <template x-if="inTable">
                        <span class="flex flex-wrap gap-0.5">
                            <span class="ed-sep"></span>
                            <button type="button" class="ed-tool" @mousedown.prevent @click="run('addRowAfter')" title="Добавить строку">+стр</button>
                            <button type="button" class="ed-tool" @mousedown.prevent @click="run('addColumnAfter')" title="Добавить столбец">+стб</button>
                            <button type="button" class="ed-tool" @mousedown.prevent @click="run('deleteRow')" title="Удалить строку">−стр</button>
                            <button type="button" class="ed-tool" @mousedown.prevent @click="run('deleteColumn')" title="Удалить столбец">−стб</button>
                            <button type="button" class="ed-tool" @mousedown.prevent @click="run('toggleHeaderRow')" title="Строка-заголовок">загл.</button>
                            <button type="button" class="ed-tool !text-bno-alarm" @mousedown.prevent @click="run('deleteTable')" title="Удалить таблицу">× табл.</button>
                        </span>
                    </template>
                    <span class="ml-auto flex">
                        <button type="button" class="ed-tool" @mousedown.prevent @click="run('undo')" title="Отменить (Ctrl+Z)">↶</button>
                        <button type="button" class="ed-tool" @mousedown.prevent @click="run('redo')" title="Повторить (Ctrl+Shift+Z)">↷</button>
                    </span>
                </div>
                <div class="ed-surface" x-ref="mount"></div>
                <input type="hidden" name="content" x-ref="input" value="{{ old('content', $page->content ? json_encode($page->content, JSON_UNESCAPED_UNICODE) : '') }}">

                {{-- Окно выбора страницы --}}
                <div x-show="picker.open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center bg-black/70 p-4 pt-24" @click.self="closePicker()">
                    <div class="w-full max-w-lg border border-bno-border bg-bno-panel">
                        <div class="border-b border-bno-border p-3">
                            <input x-ref="pickerInput" type="search" x-model="picker.q" @input.debounce.250ms="searchPages()"
                                   @keydown.enter.prevent="picker.results[0] && choosePage(picker.results[0])"
                                   placeholder="Номер или название страницы…" class="field-input">
                        </div>
                        <div class="max-h-80 overflow-auto">
                            <template x-for="r in picker.results" :key="r.id">
                                <button type="button" class="block w-full border-b border-bno-border/50 px-3 py-2 text-left text-sm hover:bg-bno-card" @click="choosePage(r)">
                                    <span class="badge" x-text="r.type"></span>
                                    <span class="ml-1 text-bno-archive" x-text="r.label"></span>
                                    <span x-show="r.status !== 'published'" class="ml-1 text-[10px] uppercase text-bno-warn">черновик</span>
                                </button>
                            </template>
                            <p x-show="!picker.loading && !picker.results.length" class="p-4 text-center text-xs text-bno-muted">Ничего не найдено.</p>
                        </div>
                    </div>
                </div>
            </div>
            <p class="mt-2 text-[11px] text-bno-muted">
                Выделите текст и нажмите «███», чтобы скрыть его цензурой. «⧉ Стр.» — ссылка на другую страницу архива: она не сломается, если страницу переименуют.
            </p>
        </div>

        {{-- Боковая колонка: публикация и поля типа --}}
        <aside class="space-y-5">
            <div class="space-y-3 border border-bno-border bg-bno-panel p-4">
                <div>
                    <label class="field-label" for="status">Статус</label>
                    <select id="status" name="status" class="field-input">
                        @foreach (\App\Models\Page::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $page->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($page->exists)
                    <div>
                        <label class="field-label" for="note">Комментарий к правке</label>
                        <input id="note" name="note" value="{{ old('note') }}" placeholder="что изменилось" class="field-input">
                    </div>
                @endif
                <div class="flex flex-wrap gap-2 pt-1">
                    <button class="btn btn-primary" name="after" value="edit">Сохранить</button>
                    <button class="btn" name="after" value="view">Сохранить и открыть</button>
                </div>
            </div>

            <div class="space-y-4 border border-bno-border bg-bno-panel p-4">
                @if ($type->numbered)
                    <div>
                        <label class="field-label" for="number">Номер</label>
                        <input id="number" type="number" min="0" name="number" value="{{ old('number', $page->number) }}" class="field-input">
                        <p class="field-help">Отображается как {{ $type->formatNumber((int) old('number', $page->number ?: 1)) }}</p>
                        @error('number')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div>
                    <label class="field-label" for="slug">Адрес страницы</label>
                    <div class="flex items-center text-xs text-bno-muted">
                        <span class="shrink-0">/{{ $type->url_prefix }}/</span>
                        <input id="slug" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="автоматически" class="field-input !px-1.5 !py-1 !text-xs">
                    </div>
                    @error('slug')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="field-label" for="summary">Краткое описание</label>
                    <textarea id="summary" name="summary" rows="3" class="field-input" placeholder="Одна-две фразы для списков и поиска">{{ old('summary', $page->summary) }}</textarea>
                </div>
                <div>
                    <label class="field-label" for="tags">Теги</label>
                    <input id="tags" name="tags" value="{{ old('tags', $page->exists ? $page->tags->pluck('name')->implode(', ') : '') }}" placeholder="через запятую" class="field-input">
                </div>
                <div>
                    <label class="field-label" for="published_at">Дата публикации</label>
                    <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', $page->published_at?->format('Y-m-d\TH:i')) }}" class="field-input">
                    <p class="field-help">Пусто — в момент публикации. Дата в будущем — отложенная публикация.</p>
                </div>
            </div>

            @if ($type->fieldDefinitions()->isNotEmpty())
                <div class="space-y-4 border border-bno-border bg-bno-panel p-4">
                    <p class="text-[11px] uppercase tracking-[0.15em] text-bno-active">Поля типа «{{ $type->name }}»</p>
                    @foreach ($type->fieldDefinitions() as $field)
                        @include('admin.pages._field', ['field' => $field, 'value' => $page->fieldValue($field['key'])])
                    @endforeach
                </div>
            @endif

            @if ($page->exists)
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <a href="{{ route('admin.pages.revisions', $page) }}" class="btn">История правок</a>
                    <button type="submit" form="delete-page" class="btn btn-danger ml-auto">Удалить</button>
                </div>
            @endif
        </aside>
    </form>

    @if ($page->exists)
        <form id="delete-page" method="post" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Удалить страницу «{{ addslashes($page->title) }}»?')">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
