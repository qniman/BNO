<article class="mx-auto max-w-4xl">
    <header class="border-b border-bno-border pb-5">
        <h1 class="font-display text-4xl uppercase tracking-[0.15em] text-bno-active">{{ $page->title }}</h1>
        @if ($page->summary)
            <p class="mt-2 font-serif text-lg text-bno-terminal">{{ $page->summary }}</p>
        @endif
        @if ($headerFields->isNotEmpty())
            <dl class="mt-3 flex flex-wrap gap-x-5 text-[11px] uppercase text-bno-muted">
                @foreach ($headerFields as $f)
                    <div>{{ $f['label'] }}: <span class="text-bno-archive">{!! $f['html'] !!}</span></div>
                @endforeach
            </dl>
        @endif
    </header>

    <div class="prose-archive mt-8 flow-root">{!! $body !!}</div>
</article>
