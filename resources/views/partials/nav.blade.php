@php
    $links = [
        ['route' => 'home', 'label' => 'Терминал'],
        ['route' => 'objects.index', 'label' => 'Объекты'],
        ['route' => 'documents.index', 'label' => 'Документы'],
        ['route' => 'incidents.index', 'label' => 'Инциденты'],
        ['route' => 'personnel.index', 'label' => 'Персонал'],
        ['route' => 'divisions.index', 'label' => 'Секции'],
        ['route' => 'access.index', 'label' => 'Доступ'],
    ];
@endphp
<nav class="border-t border-bno-border bg-black/40">
    <div class="mx-auto flex max-w-6xl flex-wrap gap-2 px-4 py-2 text-xs uppercase">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}"
               class="border border-transparent px-2 py-1 hover:border-bno-border hover:text-bno-active {{ request()->routeIs($link['route']) ? 'border-bno-border text-bno-active' : 'text-bno-muted' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
        @can('manage-registry')
            <a href="{{ route('registry.dashboard') }}" class="ml-auto border border-bno-alarm/40 px-2 py-1 text-bno-alarm hover:bg-bno-card">
                Регистратура
            </a>
        @endcan
    </div>
</nav>
