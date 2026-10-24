@if ($pages->isEmpty())
    <p class="mt-3 border border-dashed border-bno-border p-6 text-center text-sm text-bno-muted">Страниц пока нет.</p>
@else
    {{-- Телефон: карточки --}}
    <ul class="mt-3 space-y-2 md:hidden">
        @foreach ($pages as $p)
            <li class="border border-bno-border bg-bno-panel">
                <a href="{{ route('admin.pages.edit', $p) }}" class="block px-4 py-3 active:bg-bno-card">
                    <span class="flex items-center gap-2 text-[10px] uppercase text-bno-muted">
                        {{ $p->type->name }}
                        <span class="badge {{ $p->status === 'published' ? '!border-bno-active/60 !text-bno-active' : '!border-bno-warn/60 !text-bno-warn' }}">{{ \App\Models\Page::STATUSES[$p->status] ?? $p->status }}</span>
                        <span class="ml-auto">{{ $p->updated_at->format('d.m H:i') }}</span>
                    </span>
                    <span class="mt-1 block text-bno-archive">
                        @if ($p->code())<span class="text-bno-active">{{ $p->code() }}</span> · @endif{{ $p->title }}
                    </span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Компьютер: таблица --}}
    <div class="mt-3 hidden overflow-x-auto border border-bno-border bg-bno-panel md:block">
        <table class="w-full text-sm">
            <thead class="border-b border-bno-border text-left text-[11px] uppercase text-bno-muted">
                <tr>
                    <th class="px-3 py-2 font-normal">Тип</th>
                    <th class="px-3 py-2 font-normal">Страница</th>
                    <th class="px-3 py-2 font-normal">Статус</th>
                    <th class="px-3 py-2 font-normal">Изменена</th>
                    <th class="px-3 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-bno-border">
                @foreach ($pages as $p)
                    <tr class="hover:bg-bno-card">
                        <td class="px-3 py-2 text-xs text-bno-muted">{{ $p->type->name }}</td>
                        <td class="px-3 py-2">
                            <a href="{{ route('admin.pages.edit', $p) }}" class="text-bno-archive hover:text-bno-active">
                                @if ($p->code())<span class="text-bno-active">{{ $p->code() }}</span> · @endif{{ $p->title }}
                            </a>
                        </td>
                        <td class="px-3 py-2">
                            <span class="badge {{ $p->status === 'published' ? '!border-bno-active/60 !text-bno-active' : '!border-bno-warn/60 !text-bno-warn' }}">{{ \App\Models\Page::STATUSES[$p->status] ?? $p->status }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-bno-muted">{{ $p->updated_at->format('d.m.Y H:i') }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            <a href="{{ $p->url() }}" class="text-bno-muted hover:text-bno-active" target="_blank">открыть ↗</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
