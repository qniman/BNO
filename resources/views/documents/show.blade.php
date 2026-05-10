@extends('layouts.archive')

@section('title', $document->title)

@section('content')
    @php
        $shellClass = trim(implode(' ', array_filter([
            'relative overflow-hidden border bg-bno-panel p-6 doc-serif',
            $stampMeta['frame'] ?? 'border-bno-border',
            $document->leaked_publish ? 'shadow-[inset_0_0_80px_rgba(139,47,47,0.08)]' : '',
            ($document->corruption_level ?? 0) > 0
                ? 'doc-shell-corruption-'.min(3, max(1, (int) $document->corruption_level))
                : null,
        ])));
        $wm = $stampMeta['watermark'] ?? null;
        if ($document->leaked_publish) {
            $wm = ($wm ? $wm.' · ' : '').'УТЕЧКА';
        }
    @endphp

    <article class="{{ $shellClass }}">
        @if ($wm)
            <div class="doc-watermark text-bno-active" aria-hidden="true">{{ $wm }}</div>
        @endif

        <header class="relative z-[2] border-b border-bno-border pb-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase text-bno-muted">
                        {{ strtoupper($document->type) }} · ДОПУСК {{ $document->clearance_level }}
                        @if ($document->revision_label)
                            · РЕДАКЦИЯ {{ $document->revision_label }}
                        @endif
                    </p>
                    <h1 class="font-display text-4xl uppercase tracking-[0.25em] text-bno-active">{{ $document->title }}</h1>
                    @if ($stampMeta)
                        <p class="mt-3 inline-block border border-dashed px-3 py-1 text-[11px] uppercase {{ $stampMeta['accent'] ?? 'text-bno-muted' }}">{{ $stampMeta['label'] }}</p>
                    @endif
                    @if ($document->classification)
                        <p class="mt-2 text-xs uppercase text-bno-muted">{{ $document->classification }}</p>
                    @endif
                    @if ($document->published_at)
                        <p class="mt-2 text-[11px] uppercase text-bno-muted">
                            Дата публикации в узле: {{ $document->published_at->timezone(config('app.timezone'))->format('d.m.Y') }}
                            @if ($document->chronology_anomaly)
                                <span class="ml-2 text-bno-alarm">· временная метка не согласована с контекстом</span>
                            @endif
                        </p>
                    @endif
                    @if ($document->updated_at)
                        <p class="mt-1 text-[11px] uppercase text-bno-muted">Изменено в узле: {{ $document->updated_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</p>
                    @endif
                </div>
            </div>

            @if ($document->registration_number || $document->routing_note || $document->acknowledgment_stub)
                <dl class="relative z-[2] mt-4 grid gap-2 border border-bno-border bg-black/40 p-3 text-[11px] uppercase md:grid-cols-3">
                    @if ($document->registration_number)
                        <div>
                            <dt class="text-bno-muted">Зарегистрировано</dt>
                            <dd class="mt-1 font-mono text-[10px] normal-case tracking-wider text-bno-archive">{{ $document->registration_number }}</dd>
                        </div>
                    @endif
                    @if ($document->routing_note)
                        <div class="md:col-span-2">
                            <dt class="text-bno-muted">Маршрут согласования</dt>
                            <dd class="mt-1 normal-case text-bno-archive">{{ $document->routing_note }}</dd>
                        </div>
                    @endif
                    @if ($document->acknowledgment_stub)
                        <div class="md:col-span-3">
                            <dt class="text-bno-muted">Отметка</dt>
                            <dd class="mt-1 normal-case text-bno-archive">{{ $document->acknowledgment_stub }}</dd>
                        </div>
                    @endif
                </dl>
            @endif

            @if ($document->marginalia)
                <aside class="relative z-[2] mt-4 border border-dashed border-bno-border bg-black/30 px-3 py-2 text-sm italic text-bno-muted archive-md-flow">
                    {!! \App\Services\ArchiveRedactor::rich($document->marginalia) !!}
                </aside>
            @endif

            @if ($document->references_object_slug)
                <div class="relative z-[2] mt-4 border border-bno-border bg-black/35 px-3 py-2 text-sm">
                    <p class="text-[11px] uppercase text-bno-muted">Связанный объект</p>
                    @if ($referencedObject)
                        <p class="mt-2">
                            <a href="{{ route('objects.show', $referencedObject->slug) }}" class="text-bno-active hover:underline">{{ $referencedObject->number }}</a>
                            <span class="text-bno-muted"> — {{ $referencedObject->title }}</span>
                        </p>
                    @else
                        <p class="mt-2 text-bno-alarm">{{ strtoupper($document->references_object_slug) }}</p>
                        <p class="mt-1 text-xs uppercase text-bno-muted">Запись отсутствует в архиве или недоступна при вашем допуске.</p>
                    @endif
                </div>
            @endif

            @if ($document->volatile && $corruption)
                <p class="relative z-[2] mt-4 border border-bno-alarm px-3 py-2 text-xs uppercase text-bno-alarm">{{ $corruption }}</p>
            @endif
        </header>

        <div class="doc-body archive-md-flow relative z-[2] mt-6 max-w-none text-base leading-relaxed text-bno-archive doc-serif">
            {!! $body !!}
            @if ($document->corruption_level >= 2)
                <p class="mt-6 border-t border-dashed border-bno-border pt-3 font-mono text-xs uppercase text-bno-muted">[ фрагмент обрезан при последней выгрузке · восстановление не запланировано ]</p>
            @endif
        </div>

        @if ($revisionHistory->isNotEmpty())
            <section class="relative z-[2] mt-10 border-t border-bno-border pt-6">
                <h2 class="text-xs uppercase text-bno-muted">Архив редакций</h2>
                <ul class="mt-3 space-y-3 text-sm">
                    @foreach ($revisionHistory as $rev)
                        <li class="border border-bno-border bg-black/40 px-3 py-2">
                            <p class="text-[11px] uppercase text-bno-active">
                                Редакция {{ $rev->revision_label }}
                                · {{ $rev->revised_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                                @if ($rev->editor)
                                    · {{ $rev->editor->codename ?? $rev->editor->name }}
                                @endif
                            </p>
                            @if ($rev->change_reason)
                                <p class="mt-2 text-bno-archive doc-serif normal-case text-[13px] leading-snug">«{{ $rev->change_reason }}»</p>
                            @endif
                            @if ($rev->content_snapshot)
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-[11px] uppercase text-bno-muted">Текстовый снимок</summary>
                                    <pre class="mt-2 max-h-48 overflow-auto whitespace-pre-wrap border border-dashed border-bno-border bg-black/60 p-2 font-mono text-[11px] leading-relaxed text-bno-muted">{{ \Illuminate\Support\Str::limit($rev->content_snapshot, 4000) }}</pre>
                                </details>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <footer class="relative z-[2] mt-10 border-t border-bno-border pt-4 text-[11px] uppercase text-bno-muted">
            @if ($viewsLastDay > 0)
                <p>Зафиксировано открытий за последние 24 часа (журнал узла): {{ $viewsLastDay }}.</p>
            @endif
            @if ($lastView && $lastView->user)
                <p class="mt-2">
                    Предыдущий зафиксированный просмотр:
                    оператор «{{ $lastView->user->codename ?? $lastView->user->name }}»
                    · {{ $lastView->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                </p>
            @elseif ($lastView)
                <p class="mt-2">Предыдущий зафиксированный просмотр: сеанс без идентификации оператора · {{ $lastView->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</p>
            @endif
        </footer>
    </article>
@endsection
