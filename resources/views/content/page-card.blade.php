<a href="{{ $page->url() }}" class="blk-card no-underline hover:border-bno-active">
    <span class="badge">{{ $page->type->name }}</span>
    <span class="mt-1 block text-bno-active">{{ $page->fullTitle() }}</span>
    @if ($page->summary)
        <span class="mt-1 block font-serif text-sm text-bno-terminal">{{ \Illuminate\Support\Str::limit($page->summary, 200) }}</span>
    @endif
</a>
