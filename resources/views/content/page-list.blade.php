<div class="blk-pagelist">
    @if ($title !== '')
        <p class="mb-2 text-xs uppercase tracking-[0.15em] text-bno-muted">{{ $title }}</p>
    @endif
    @if ($pages->isEmpty())
        <p class="text-bno-muted">Записей пока нет.</p>
    @else
        <ul class="divide-y divide-bno-border border border-bno-border">
            @foreach ($pages as $p)
                <li>
                    <a href="{{ $p->url() }}" class="flex gap-3 px-3 py-2 no-underline hover:bg-bno-card">
                        @if ($p->code())
                            <span class="shrink-0 text-bno-active">{{ $p->code() }}</span>
                        @endif
                        <span class="text-bno-archive">{{ $p->title }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
