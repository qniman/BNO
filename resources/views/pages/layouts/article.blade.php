<article class="mx-auto max-w-3xl">
    <header class="border-b border-bno-border pb-6">
        @if ($page->code())
            <p class="text-sm text-bno-active">{{ $page->code() }}</p>
        @endif
        <h1 class="font-serif text-4xl leading-tight text-bno-archive">{{ $page->title }}</h1>
        @if ($page->summary)
            <p class="mt-3 font-serif text-lg italic leading-relaxed text-bno-terminal">{{ $page->summary }}</p>
        @endif
        <div class="mt-4 flex flex-wrap gap-x-5 gap-y-1 text-[11px] uppercase text-bno-muted">
            @if ($page->published_at)
                <span>{{ $page->published_at->translatedFormat('j F Y') }}</span>
            @endif
            @foreach ($headerFields as $f)
                <span>{{ $f['label'] }}: <span class="text-bno-archive">{!! $f['html'] !!}</span></span>
            @endforeach
        </div>
    </header>

    <div class="prose-archive mt-8 flow-root">{!! $body !!}</div>
</article>
