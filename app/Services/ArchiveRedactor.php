<?php

namespace App\Services;

use League\CommonMark\GithubFlavoredMarkdownConverter;

final class ArchiveRedactor
{
    private static ?GithubFlavoredMarkdownConverter $markdown = null;

    private static function markdown(): GithubFlavoredMarkdownConverter
    {
        return self::$markdown ??= new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 15,
        ]);
    }

    /**
     * Текст лора + маркеры [[[…]]] + Markdown (GFM) в безопасный HTML.
     */
    public static function rich(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        $segments = preg_split('/(\[\[\[.+?\]\]\])/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $html = '';
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            if (preg_match('/^\[\[\[(.+?)\]\]\]$/s', $segment)) {
                $html .= '<span class="redacted" title="Сведения удалены">██████████</span>';

                continue;
            }
            $fragment = self::markdown()->convert($segment)->getContent();
            if ($fragment !== '') {
                $html .= '<div class="archive-md-root">'.$fragment.'</div>';
            }
        }

        return $html;
    }

    /** Совместимость: то же, что {@see rich()}. */
    public static function inline(?string $text): string
    {
        return self::rich($text);
    }

    public static function stripMarkers(string $text): string
    {
        return preg_replace('/\[\[\[(.+?)\]\]\]/', '██████████', $text) ?? $text;
    }
}
