@props([
    'name',
    'value' => '',
    'placeholder' => '',
    'minHeight' => 220,
])
@php
    $fieldMinH = max(120, (int) $minHeight);
@endphp

<div
    {{ $attributes->merge(['class' => 'registry-markdown-field relative z-0 flex w-full min-w-0 flex-col border border-bno-border bg-black']) }}
    x-data="registryMarkdownEditor(@js(['placeholder' => $placeholder, 'minHeight' => $fieldMinH]))"
>
    {{-- hidden: не в потоке и не перехватывает клики (sr-only + absolute мог накрывать ProseMirror) --}}
    <textarea x-ref="mdSource" name="{{ $name }}" hidden tabindex="-1" autocomplete="off">{{ $value }}</textarea>

    <div
        x-ref="mdToolbar"
        class="relative z-[1] flex flex-wrap gap-1 border-b border-bno-border bg-bno-panel px-1 py-1"
        role="toolbar"
        aria-label="Форматирование Markdown"
    >
        <button type="button" data-registry-cmd="bold" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Жирный">B</button>
        <button type="button" data-registry-cmd="italic" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Курсив">I</button>
        <button type="button" data-registry-cmd="h2" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Заголовок 2">H2</button>
        <button type="button" data-registry-cmd="h3" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Заголовок 3">H3</button>
        <button type="button" data-registry-cmd="bullet" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Маркированный список">•</button>
        <button type="button" data-registry-cmd="ordered" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Нумерованный список">1.</button>
        <button type="button" data-registry-cmd="quote" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Цитата">&gt;</button>
        <button type="button" data-registry-cmd="code" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Блок кода"><span class="font-mono normal-case">&lcub;&rcub;</span></button>
        <span class="grow"></span>
        <button type="button" data-registry-cmd="undo" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Отменить">↺</button>
        <button type="button" data-registry-cmd="redo" class="border border-bno-border px-2 py-0.5 text-[11px] uppercase text-bno-muted hover:border-bno-active hover:text-bno-active" title="Вернуть">↻</button>
    </div>

    <div
        x-ref="mdMount"
        class="registry-tiptap-mount relative z-[2] w-full shrink-0 bg-black"
        style="min-height: {{ $fieldMinH }}px"
    >
        {{-- x-ignore на том же элементе, что и x-ref, ломает ref (ignore выполняется раньше в Alpine). Ищем узел по data-атрибуту из JS. --}}
        <div class="registry-tiptap-inner min-h-[inherit] w-full" data-registry-tiptap-root x-ignore></div>
    </div>
</div>
