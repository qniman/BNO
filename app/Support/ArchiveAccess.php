<?php

namespace App\Support;

final class ArchiveAccess
{
    /** Эффективный уровень доступа к материалам архива (0–5). Директор всегда имеет полный доступ на чтение. */
    public static function clearance(): int
    {
        $user = auth()->user();

        if ($user === null) {
            return 1;
        }

        if ($user->role === 'director') {
            return 5;
        }

        return max(0, min(5, (int) $user->clearance_level));
    }

    public static function role(): ?string
    {
        return auth()->user()?->role;
    }

    /** Полное управление регистратурой и системными страницами: директор всегда, также уровень 5 и руководители. */
    public static function canManageRegistry(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'director') {
            return true;
        }

        if ($user->clearance_level >= 5) {
            return true;
        }

        return $user->role === 'manager';
    }

    public static function isDirector(): bool
    {
        return auth()->user()?->role === 'director';
    }
}
