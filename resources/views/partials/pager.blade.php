@if ($paginator->hasPages())
    <nav class="mt-6 flex items-center justify-between text-xs uppercase">
        @if ($paginator->onFirstPage())
            <span class="text-bno-border">← Назад</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn">← Назад</a>
        @endif
        <span class="text-bno-muted">Лист {{ $paginator->currentPage() }} из {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn">Далее →</a>
        @else
            <span class="text-bno-border">Далее →</span>
        @endif
    </nav>
@endif
