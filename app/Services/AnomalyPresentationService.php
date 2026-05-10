<?php

namespace App\Services;

use App\Models\ObjectEntity;
use Illuminate\Support\Facades\Cache;

class AnomalyPresentationService
{
    /**
     * @return array{paper_count: ?int, glitch_class: string, whisper: ?string}
     */
    public function forObject(ObjectEntity $object): array
    {
        if ($object->slug !== 'bno-311') {
            return ['paper_count' => null, 'glitch_class' => '', 'whisper' => null];
        }

        $seed = (int) Cache::remember(
            'bno311:seed:'.request()->session()->getId(),
            3600,
            fn () => random_int(1000, 999999)
        );

        $paperCount = 12 + (($seed + (int) (microtime(true) * 10)) % 37);

        $classes = ['', ' crt-flicker', ' scan-shift'];
        $glitch = $classes[$seed % 3];

        $whispers = [
            null,
            'несовпадение инвентарной описи',
            'повторная верификация требуется',
        ];

        return [
            'paper_count' => $paperCount,
            'glitch_class' => $glitch,
            'whisper' => $whispers[$seed % 3],
        ];
    }

    /**
     * @return array{mode: string, fragment: ?string}
     */
    public function ghostRecordState(string $slug): array
    {
        if ($slug !== 'bno-001') {
            return ['mode' => 'normal', 'fragment' => null];
        }

        $key = 'ghost:bno001:'.request()->session()->getId();
        $mode = Cache::remember($key, 900, fn () => ['deleted', 'leak', 'static'][random_int(0, 2)]);

        $fragments = [
            '…убить канал. Они уже видели вас.',
            'Круг сохранился в почве. Контрольный образец утрачен.',
            'Память места не подлежит архивации.',
        ];

        return match ($mode) {
            'leak' => ['mode' => 'leak', 'fragment' => $fragments[array_rand($fragments)]],
            'static' => ['mode' => 'static', 'fragment' => null],
            default => ['mode' => 'deleted', 'fragment' => null],
        };
    }

    /**
     * @return array{level: int, label: string}
     */
    public function threatSnapshot(): array
    {
        $ticks = (int) floor(now()->timestamp / 120);
        $level = 2 + ($ticks % 3);

        return [
            'level' => $level,
            'label' => match ($level) {
                2 => 'ПОВЫШЕННЫЙ',
                3 => 'СРЕДНИЙ',
                default => 'ЛОКАЛЬНЫЙ ПИК',
            },
        ];
    }
}
