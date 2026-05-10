@extends('layouts.archive')

@section('title', 'Журнал аудита')

@section('content')
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-bno-border pb-4">
        <div>
            <p class="text-xs uppercase text-bno-muted">Регистратура</p>
            <h1 class="font-display text-3xl uppercase tracking-[0.35em] text-bno-active">Журнал аудита</h1>
        </div>
        <a href="{{ route('registry.dashboard') }}" class="border border-bno-border px-4 py-2 text-xs uppercase text-bno-muted hover:border-bno-active hover:text-bno-active">← Узел</a>
    </header>

    <div class="overflow-x-auto border border-bno-border bg-black">
        <table class="min-w-full divide-y divide-bno-border text-left text-[11px] uppercase">
            <thead class="bg-bno-panel text-bno-muted">
            <tr>
                <th class="px-3 py-2">Время</th>
                <th class="px-3 py-2">Действие</th>
                <th class="px-3 py-2">Пользователь</th>
                <th class="px-3 py-2">Субъект</th>
                <th class="px-3 py-2">IP</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-bno-border text-bno-archive">
            @foreach ($logs as $log)
                <tr class="align-top">
                    <td class="px-3 py-2 whitespace-nowrap text-bno-muted">{{ $log->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i:s') }}</td>
                    <td class="px-3 py-2 text-bno-active">{{ $log->action }}</td>
                    <td class="px-3 py-2">{{ $log->user?->codename ?? $log->user?->email ?? '—' }}</td>
                    <td class="px-3 py-2 normal-case">
                        {{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}
                        @if ($log->meta)
                            <span class="mt-1 block text-[10px] text-bno-muted">{{ json_encode($log->meta, JSON_UNESCAPED_UNICODE) }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-bno-muted">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-xs text-bno-muted">
        {{ $logs->links() }}
    </div>
@endsection
