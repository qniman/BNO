@extends('layouts.archive')

@section('title', $object->number.' — '.$object->title)

@section('content')
    @php
        $classification = trim(implode(' / ', array_filter([
            $object->threat_class,
            $object->anomaly_type,
            $object->containment_level,
            $object->stability_index,
            $object->psi_index,
            $object->amnestic_index,
            $object->interaction_index,
        ])));
    @endphp

    <article class="{{ trim(($presentationHints['glitch_class'] ?? '').' border border-bno-border bg-bno-panel p-6') }}">
        <header class="border-b border-bno-border pb-4">
            <p class="text-xs uppercase text-bno-muted">Карточка объекта</p>
            <h1 class="font-display text-4xl uppercase tracking-[0.18em] text-bno-active">
                {{ $object->number }} — {{ $object->title }}
            </h1>
            @if(! empty($presentationHints['paper_count']))
                <p class="mt-2 text-xs uppercase text-bno-alarm">
                    Несоответствие инвентаризации: зафиксировано {{ $presentationHints['paper_count'] }} листов при последней выборке.
                </p>
            @endif
            @if(! empty($presentationHints['whisper']))
                <p class="mt-2 text-[11px] uppercase text-bno-muted">{{ $presentationHints['whisper'] }}</p>
            @endif
            <dl class="mt-4 grid gap-3 text-xs uppercase md:grid-cols-2">
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Классификация</dt>
                    <dd class="mt-1 text-bno-archive doc-serif normal-case">{{ $classification !== '' ? $classification : 'Не классифицировано' }}</dd>
                </div>
                <div class="border border-bno-border bg-black px-3 py-2">
                    <dt class="text-bno-muted">Статус</dt>
                    <dd class="mt-1 text-bno-active">{{ $object->status }}</dd>
                </div>
            </dl>
        </header>

        <section class="mt-6 space-y-6 text-sm leading-relaxed text-bno-archive doc-serif">
            <div>
                <h2 class="text-xs uppercase text-bno-muted">Протокол содержания</h2>
                <div class="archive-md-flow mt-2 text-sm leading-relaxed">{!! \App\Services\ArchiveRedactor::rich($object->containment_protocol ?? 'Сведения недоступны.') !!}</div>
            </div>
            <div>
                <h2 class="text-xs uppercase text-bno-muted">Описание</h2>
                <div class="archive-md-flow mt-2 text-sm leading-relaxed">{!! \App\Services\ArchiveRedactor::rich($object->description ?? '') !!}</div>
            </div>
            <div>
                <h2 class="text-xs uppercase text-bno-muted">История обнаружения</h2>
                <div class="archive-md-flow mt-2 text-sm leading-relaxed">{!! \App\Services\ArchiveRedactor::rich($object->history ?? '') !!}</div>
            </div>
        </section>

        @isset($linkedObjects)
            <section class="mt-8 border-t border-bno-border pt-6">
                <h2 class="text-xs uppercase text-bno-muted">Связанные объекты</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($linkedObjects as $rel)
                        @php
                            $pivot = $rel->pivot;
                        @endphp
                        <li class="flex flex-wrap items-baseline justify-between gap-2 border border-bno-border bg-black px-3 py-2">
                            <span>
                                <a href="{{ route('objects.show', $rel->slug) }}" class="text-bno-active hover:underline">{{ $rel->number }}</a>
                                <span class="text-bno-archive doc-serif normal-case"> — {{ $rel->title }}</span>
                            </span>
                            @if ($pivot && $pivot->relation_type)
                                <span class="text-[10px] uppercase text-bno-muted">{{ $pivot->relation_type }}</span>
                            @endif
                        </li>
                    @empty
                        <li class="text-xs uppercase text-bno-muted">Нет связей с вашим допуском.</li>
                    @endforelse
                </ul>
            </section>
        @endisset

        <section class="mt-8 border-t border-bno-border pt-6">
            <h2 class="text-xs uppercase text-bno-muted">Инциденты</h2>
            <ul class="mt-3 space-y-3 text-sm text-bno-archive">
                @forelse ($object->incidents as $incident)
                    <li class="border border-bno-border bg-black px-3 py-2">
                        <p class="text-[11px] uppercase text-bno-active">{{ $incident->occurred_on?->format('d.m.Y') ?? '—' }} · {{ $incident->severity }}</p>
                        <p class="mt-1">
                            <a href="{{ route('incidents.show', $incident) }}" class="hover:text-bno-active hover:underline">{{ $incident->title }}</a>
                        </p>
                        <p class="mt-1 text-xs text-bno-muted">{{ $incident->casualties }}</p>
                    </li>
                @empty
                    <li class="text-bno-muted text-xs uppercase">Нет инцидентов с вашим допуском.</li>
                @endforelse
            </ul>
        </section>

        <section class="mt-8 border-t border-bno-border pt-6">
            <h2 class="text-xs uppercase text-bno-muted">Журнал наблюдения</h2>
            <ul class="mt-3 space-y-3 text-sm">
                @forelse ($object->observationLogs as $log)
                    <li class="border border-dashed border-bno-border px-3 py-2">
                        <p class="text-[11px] uppercase text-bno-muted">{{ $log->logged_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</p>
                        <p class="mt-1 text-bno-archive doc-serif">{{ $log->entry }}</p>
                    </li>
                @empty
                    <li class="text-xs uppercase text-bno-muted">Журнал пуст или недоступен.</li>
                @endforelse
            </ul>
        </section>

        <section class="mt-8 border-t border-bno-border pt-6">
            <h2 class="text-xs uppercase text-bno-muted">Приложения</h2>
            <div class="mt-3 grid gap-4 md:grid-cols-2">
                @forelse ($object->attachments as $attachment)
                    <figure class="border border-bno-border bg-black p-3">
                        <figcaption class="mb-2 text-[11px] uppercase text-bno-muted">
                            {{ $attachment->label ?: strtoupper($attachment->kind) }} · Д{{ $attachment->clearance_level }}
                        </figcaption>
                        @switch($attachment->kind)
                            @case('audio')
                                <audio controls class="w-full" preload="none">
                                    <source src="{{ $attachment->publicUrl() }}" />
                                </audio>
                                @break
                            @case('video')
                                <video controls class="max-h-64 w-full border border-bno-border bg-black" preload="metadata">
                                    <source src="{{ $attachment->publicUrl() }}" />
                                </video>
                                @break
                            @default
                                <a href="{{ $attachment->publicUrl() }}" target="_blank" rel="noopener"
                                   class="block overflow-hidden border border-bno-border bg-bno-panel">
                                    <img src="{{ $attachment->publicUrl() }}" alt="" class="max-h-72 w-full object-contain">
                                </a>
                        @endswitch
                    </figure>
                @empty
                    <p class="text-xs uppercase text-bno-muted md:col-span-2">Нет приложений с вашим допуском.</p>
                @endforelse
            </div>
        </section>

        <section class="mt-8 border-t border-bno-border pt-6">
            <h2 class="text-xs uppercase text-bno-muted">Аддендумы</h2>
            <div class="mt-3 space-y-4">
                @forelse ($object->appendices as $appendix)
                    <article class="border border-bno-border bg-black px-4 py-3 {{ $appendix->corrupted ? 'opacity-70' : '' }}">
                        <p class="text-[11px] uppercase text-bno-active">{{ $appendix->label ?? 'Дополнение' }}</p>
                        <div class="archive-md-flow mt-2 text-sm text-bno-archive doc-serif {{ $appendix->corrupted ? 'opacity-90' : '' }}">{!! \App\Services\ArchiveRedactor::rich($appendix->body) !!}</div>
                    </article>
                @empty
                    <p class="text-xs uppercase text-bno-muted">Аддендумы отсутствуют.</p>
                @endforelse
            </div>
        </section>
    </article>
@endsection
