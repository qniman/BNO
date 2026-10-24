<?php

namespace App\Content;

/**
 * Разбор JSON-документа редактора: чистый текст для поиска и id страниц, на которые он ссылается.
 */
class ContentAnalyzer
{
    public static function plainText(?array $node): string
    {
        if (! $node) {
            return '';
        }

        $type = $node['type'] ?? '';

        if ($type === 'text') {
            $isRedacted = collect($node['marks'] ?? [])->contains('type', 'redacted');

            return $isRedacted ? '' : (string) ($node['text'] ?? '');
        }

        $text = '';
        foreach (['kind', 'title', 'meta', 'label', 'caption', 'text'] as $attr) {
            if (! empty($node['attrs'][$attr]) && is_string($node['attrs'][$attr])) {
                $text .= $node['attrs'][$attr]."\n";
            }
        }
        foreach ($node['content'] ?? [] as $child) {
            $text .= self::plainText($child);
        }

        if ($type === 'hardBreak') {
            return "\n";
        }

        // Инлайновые узлы склеиваются, блочные — с переводом строки.
        return in_array($type, ['footnote', 'doc'], true) ? $text : rtrim($text)."\n";
    }

    /** @return list<int> */
    public static function linkedPageIds(?array $node): array
    {
        $ids = [];
        self::collectIds($node ?? [], $ids);

        return array_values(array_unique($ids));
    }

    private static function collectIds(array $node, array &$ids): void
    {
        if (($node['type'] ?? '') === 'pageCard' && ! empty($node['attrs']['pageId'])) {
            $ids[] = (int) $node['attrs']['pageId'];
        }
        foreach ($node['marks'] ?? [] as $mark) {
            if (($mark['type'] ?? '') === 'pageLink' && ! empty($mark['attrs']['pageId'])) {
                $ids[] = (int) $mark['attrs']['pageId'];
            }
        }
        foreach ($node['content'] ?? [] as $child) {
            self::collectIds($child, $ids);
        }
    }
}
