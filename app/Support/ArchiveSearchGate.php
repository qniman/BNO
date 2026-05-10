<?php

namespace App\Support;

final class ArchiveSearchGate
{
    public static function restrictedMessage(?string $query): ?string
    {
        if ($query === null) {
            return null;
        }

        $trim = trim($query);
        if ($trim === '') {
            return null;
        }

        foreach (config('bno.restricted_search_patterns', []) as $pattern) {
            if (@preg_match($pattern, $trim) === 1) {
                return config('bno.restricted_search_message', 'Запрос зарегистрирован.');
            }
        }

        return null;
    }
}
