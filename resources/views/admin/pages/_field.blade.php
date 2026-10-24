@php
    $name = 'fields['.$field['key'].']';
    $value = old('fields.'.$field['key'], $value);
@endphp

<div>
    @switch($field['type'])
        @case('textarea')
            <label class="field-label" for="f-{{ $field['key'] }}">{{ $field['label'] }}@if (! empty($field['required'])) *@endif</label>
            <textarea id="f-{{ $field['key'] }}" name="{{ $name }}" rows="3" class="field-input">{{ $value }}</textarea>
            @break

        @case('select')
            <label class="field-label" for="f-{{ $field['key'] }}">{{ $field['label'] }}@if (! empty($field['required'])) *@endif</label>
            <select id="f-{{ $field['key'] }}" name="{{ $name }}" class="field-input">
                <option value="">—</option>
                @foreach ($field['options'] ?? [] as $option)
                    <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @break

        @case('boolean')
            <input type="hidden" name="{{ $name }}" value="0">
            <label class="flex items-center gap-2 text-sm text-bno-archive">
                <input type="checkbox" name="{{ $name }}" value="1" @checked($value) class="accent-bno-active">
                {{ $field['label'] }}
            </label>
            @break

        @case('page')
        @case('pages')
            @php
                $multiple = $field['type'] === 'pages';
                $selected = collect((array) $value)
                    ->map(fn ($id) => $linkedPages->get((int) $id))
                    ->filter()
                    ->map(fn ($p) => ['id' => $p->id, 'label' => $p->fullTitle(), 'type' => $p->type->name])
                    ->values();
            @endphp
            <span class="field-label">{{ $field['label'] }}@if (! empty($field['required'])) *@endif</span>
            <div x-data="pagePicker({ url: @js(route('admin.lookup.pages')), type: @js($field['page_type'] ?? null), multiple: @js($multiple), selected: @js($selected) })" class="relative">
                <template x-for="p in selected" :key="p.id">
                    <div class="mb-1 flex items-center gap-2 border border-bno-border bg-black px-2 py-1 text-xs">
                        <input type="hidden" name="{{ $name }}{{ $multiple ? '[]' : '' }}" :value="p.id">
                        <span class="text-bno-archive" x-text="p.label"></span>
                        <button type="button" class="ml-auto text-bno-muted hover:text-bno-alarm" @click="remove(p.id)">×</button>
                    </div>
                </template>
                <input type="search" x-model="q" @input.debounce.250ms="search()" @focus="search()" @click.outside="open = false"
                       placeholder="{{ $multiple ? 'Добавить страницу…' : 'Выбрать страницу…' }}" class="field-input !py-1 !text-xs">
                <div x-show="open && results.length" x-cloak class="absolute z-30 mt-1 max-h-60 w-full overflow-auto border border-bno-border bg-bno-panel">
                    <template x-for="r in results" :key="r.id">
                        <button type="button" class="block w-full px-2 py-1.5 text-left text-xs hover:bg-bno-card" @click="add(r)">
                            <span class="text-bno-muted" x-text="r.type + ' · '"></span><span class="text-bno-archive" x-text="r.label"></span>
                        </button>
                    </template>
                </div>
            </div>
            @break

        @default
            <label class="field-label" for="f-{{ $field['key'] }}">{{ $field['label'] }}@if (! empty($field['required'])) *@endif</label>
            <input id="f-{{ $field['key'] }}"
                   type="{{ ['number' => 'number', 'date' => 'date'][$field['type']] ?? 'text' }}"
                   @if ($field['type'] === 'number') step="any" @endif
                   name="{{ $name }}" value="{{ $value }}" class="field-input">
    @endswitch

    @if (! empty($field['help']))
        <p class="field-help">{{ $field['help'] }}</p>
    @endif
    @error('fields.'.$field['key'])
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
