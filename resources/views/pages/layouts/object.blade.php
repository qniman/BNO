<article class="mx-auto max-w-4xl">
    <header class="border border-bno-border bg-bno-panel">
        <div class="flex flex-wrap items-end justify-between gap-3 border-b border-bno-border px-5 py-4">
            <div>
                @if ($page->code())
                    <p class="font-display text-4xl leading-none md:text-5xl tracking-[0.15em] text-bno-active">{{ $page->code() }}</p>
                @endif
                <h1 class="mt-2 font-serif text-2xl text-bno-archive">{{ $page->title }}</h1>
            </div>
            <p class="text-[10px] uppercase text-bno-muted">Карточка объекта</p>
        </div>
        @if ($headerFields->isNotEmpty())
            <dl class="grid grid-cols-2 text-xs {{ ['', 'sm:grid-cols-1', 'sm:grid-cols-2', 'sm:grid-cols-3', 'sm:grid-cols-4'][min(4, $headerFields->count())] }}">
                @foreach ($headerFields as $f)
                    <div class="border-b border-r border-bno-border px-5 py-3 last:border-r-0">
                        <dt class="text-[10px] uppercase text-bno-muted">{{ $f['label'] }}</dt>
                        <dd class="mt-1 text-sm uppercase tracking-wide text-bno-archive">{!! $f['html'] !!}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
        @if ($page->summary)
            <p class="px-5 py-3 font-serif italic text-bno-terminal">{{ $page->summary }}</p>
        @endif
    </header>

    <div class="prose-archive mt-8 flow-root">{!! $body !!}</div>
</article>
