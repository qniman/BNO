@php
    // Первое поле-список из шапки выводится штампом (например, гриф секретности).
    $stamp = $headerFields->firstWhere('type', 'select');
    $details = $headerFields->reject(fn ($f) => $stamp && $f['key'] === $stamp['key']);
@endphp

<article class="paper-sheet mx-auto max-w-3xl px-6 py-8 md:px-12 md:py-12">
    <header class="relative border-b-2 border-double border-bno-ink/50 pb-5 text-center">
        <p class="font-sans text-[11px] uppercase tracking-[0.3em]">Бюро нетипичных объектов</p>
        <p class="mt-1 font-sans text-[10px] uppercase tracking-[0.2em] text-bno-ink/60">{{ $type->name }}@if ($page->code()) № {{ $page->code() }}@endif</p>
        <h1 class="mt-4 font-serif text-2xl font-bold leading-snug">{{ $page->title }}</h1>
        @if ($page->summary)
            <p class="mt-2 font-serif italic text-bno-ink/70">{{ $page->summary }}</p>
        @endif
        @if ($stamp)
            <div class="absolute -top-3 right-0"><span class="stamp">{!! $stamp['html'] !!}</span></div>
        @endif
    </header>

    @if ($details->isNotEmpty())
        <dl class="mt-5 grid gap-x-6 gap-y-1 font-sans text-xs sm:grid-cols-2">
            @foreach ($details as $f)
                <div class="flex gap-2 border-b border-dotted border-bno-ink/30 py-1">
                    <dt class="shrink-0 uppercase text-bno-ink/60">{{ $f['label'] }}:</dt>
                    <dd>{!! $f['html'] !!}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    <div class="prose-archive mt-8 flow-root">{!! $body !!}</div>
</article>
