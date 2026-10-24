<?php

namespace App\Content;

use App\Models\Page;
use App\Models\PageType;
use Illuminate\Support\Collection;

/**
 * Превращает JSON-документ редактора (Tiptap / ProseMirror) в HTML.
 *
 * Текст всегда экранируется; ссылки и картинки пропускаются только с безопасными схемами.
 * Ссылки на страницы хранятся по id, поэтому переименование страницы их не ломает.
 */
class ContentRenderer
{
    /** @var Collection<int, Page> */
    private Collection $pages;

    /** @var list<string> */
    private array $footnotes = [];

    public function render(?array $doc): string
    {
        if (empty($doc['content'])) {
            return '';
        }

        $this->footnotes = [];
        $this->pages = Page::with('type')
            ->whereIn('id', ContentAnalyzer::linkedPageIds($doc))
            ->get()
            ->keyBy('id');

        $html = $this->children($doc);

        if ($this->footnotes !== []) {
            $html .= '<section class="blk-footnotes"><h2>Примечания</h2><ol>';
            foreach ($this->footnotes as $i => $text) {
                $n = $i + 1;
                $html .= '<li id="fn-'.$n.'">'.e($text).' <a href="#fnref-'.$n.'" class="blk-footnote-back">↩</a></li>';
            }
            $html .= '</ol></section>';
        }

        return $html;
    }

    private function children(array $node): string
    {
        $out = '';
        foreach ($node['content'] ?? [] as $child) {
            $out .= $this->node($child);
        }

        return $out;
    }

    private function node(array $node): string
    {
        $attrs = $node['attrs'] ?? [];
        $inner = fn () => $this->children($node);

        return match ($node['type'] ?? '') {
            'text' => $this->text($node),
            'paragraph' => '<p'.$this->alignAttr($attrs).'>'.$inner().'</p>',
            'heading' => $this->heading($attrs, $inner()),
            'bulletList' => '<ul>'.$inner().'</ul>',
            'orderedList' => '<ol'.(($attrs['start'] ?? 1) != 1 ? ' start="'.(int) $attrs['start'].'"' : '').'>'.$inner().'</ol>',
            'listItem' => '<li>'.$inner().'</li>',
            'blockquote' => '<blockquote>'.$inner().'</blockquote>',
            'codeBlock' => '<pre><code>'.$this->plain($node).'</code></pre>',
            'horizontalRule' => '<hr>',
            'hardBreak' => '<br>',
            'table' => '<div class="blk-table"><table>'.$inner().'</table></div>',
            'tableRow' => '<tr>'.$inner().'</tr>',
            'tableHeader' => $this->cell('th', $attrs, $inner()),
            'tableCell' => $this->cell('td', $attrs, $inner()),
            'collapsible' => $this->collapsible($attrs, $inner()),
            'restricted' => $this->restricted($attrs, $inner()),
            'docBlock' => $this->docBlock($attrs, $inner()),
            'callout' => $this->callout($attrs, $inner()),
            'image' => $this->image($attrs),
            'pageList' => $this->pageList($attrs),
            'pageCard' => $this->pageCard($attrs),
            'footnote' => $this->footnote($attrs),
            default => $inner(),
        };
    }

    private function text(array $node): string
    {
        $text = (string) ($node['text'] ?? '');
        $marks = $node['marks'] ?? [];

        // Цензура заменяет сам текст: в HTML исходник не попадает.
        if (collect($marks)->contains('type', 'redacted')) {
            $len = max(3, mb_strlen($text));

            return '<span class="redacted" title="Данные изъяты">'.str_repeat('█', min($len, 60)).'</span>';
        }

        $html = nl2br(e($text), false);

        foreach ($marks as $mark) {
            $a = $mark['attrs'] ?? [];
            $html = match ($mark['type'] ?? '') {
                'bold' => "<strong>{$html}</strong>",
                'italic' => "<em>{$html}</em>",
                'underline' => "<u>{$html}</u>",
                'strike' => "<s>{$html}</s>",
                'code' => "<code>{$html}</code>",
                'link' => $this->link($a, $html),
                'pageLink' => $this->pageLinkMark($a, $html),
                default => $html,
            };
        }

        return $html;
    }

    private function heading(array $attrs, string $inner): string
    {
        $level = min(4, max(2, (int) ($attrs['level'] ?? 2)));

        return "<h{$level}".$this->alignAttr($attrs).">{$inner}</h{$level}>";
    }

    private function alignAttr(array $attrs): string
    {
        $align = $attrs['textAlign'] ?? null;

        return in_array($align, ['center', 'right', 'justify'], true) ? ' style="text-align:'.$align.'"' : '';
    }

    private function cell(string $tag, array $attrs, string $inner): string
    {
        $span = '';
        if (($attrs['colspan'] ?? 1) > 1) {
            $span .= ' colspan="'.(int) $attrs['colspan'].'"';
        }
        if (($attrs['rowspan'] ?? 1) > 1) {
            $span .= ' rowspan="'.(int) $attrs['rowspan'].'"';
        }

        return "<{$tag}{$span}>{$inner}</{$tag}>";
    }

    private function link(array $attrs, string $html): string
    {
        $href = self::safeUrl($attrs['href'] ?? '');
        if ($href === null) {
            return $html;
        }
        $external = str_starts_with($href, 'http');

        return '<a href="'.e($href).'"'.($external ? ' target="_blank" rel="noopener nofollow"' : '').'>'.$html.'</a>';
    }

    private function pageLinkMark(array $attrs, string $html): string
    {
        $page = $this->pages->get((int) ($attrs['pageId'] ?? 0));

        if (! $page || ! $this->visible($page)) {
            return '<span class="page-link-missing" title="Запись отсутствует в архиве">'.$html.'</span>';
        }

        return '<a href="'.e($page->url()).'" class="page-link">'.$html.'</a>';
    }

    private function collapsible(array $attrs, string $inner): string
    {
        $title = e($attrs['title'] ?? 'Подробнее');
        $open = ! empty($attrs['open']) ? ' open' : '';

        return "<details class=\"blk-collapsible\"{$open}><summary>{$title}</summary><div class=\"blk-body\">{$inner}</div></details>";
    }

    private function restricted(array $attrs, string $inner): string
    {
        $level = (int) ($attrs['level'] ?? 3);
        $label = e($attrs['label'] ?? '');

        return <<<HTML
<section class="blk-restricted" x-data="{ open: false, busy: false }">
    <header>Доступ уровня {$level}{$this->suffix($label)}</header>
    <div class="blk-restricted-lock" x-show="!open">
        <p>Следующий фрагмент требует допуска уровня {$level} или выше.</p>
        <button type="button" @click="busy = true; setTimeout(() => { open = true; busy = false }, 900)" :disabled="busy">
            <span x-text="busy ? 'Проверка допуска…' : 'Предъявить допуск'">Предъявить допуск</span>
        </button>
    </div>
    <div class="blk-body" x-show="open" x-cloak>{$inner}</div>
</section>
HTML;
    }

    private function docBlock(array $attrs, string $inner): string
    {
        $kind = e($attrs['kind'] ?? 'Документ');
        $title = e($attrs['title'] ?? '');
        $meta = e($attrs['meta'] ?? '');

        return '<section class="blk-doc"><header><span class="blk-doc-kind">'.$kind.'</span>'
            .($title !== '' ? '<span class="blk-doc-title">'.$title.'</span>' : '')
            .($meta !== '' ? '<span class="blk-doc-meta">'.$meta.'</span>' : '')
            .'</header><div class="blk-body">'.$inner.'</div></section>';
    }

    private function callout(array $attrs, string $inner): string
    {
        $variant = in_array($attrs['variant'] ?? '', ['info', 'warning', 'danger'], true) ? $attrs['variant'] : 'info';
        $title = e($attrs['title'] ?? '');

        return '<aside class="blk-callout blk-callout-'.$variant.'">'
            .($title !== '' ? '<header>'.$title.'</header>' : '')
            .'<div class="blk-body">'.$inner.'</div></aside>';
    }

    private function image(array $attrs): string
    {
        $src = self::safeUrl($attrs['src'] ?? '');
        if ($src === null) {
            return '';
        }
        $size = in_array($attrs['size'] ?? '', ['full', 'half', 'small'], true) ? $attrs['size'] : 'full';
        $align = in_array($attrs['align'] ?? '', ['left', 'right', 'center'], true) ? $attrs['align'] : 'center';
        $caption = trim((string) ($attrs['caption'] ?? ''));

        return '<figure class="blk-image blk-image-'.$size.' blk-image-'.$align.'">'
            .'<img src="'.e($src).'" alt="'.e($attrs['alt'] ?? $caption).'" loading="lazy">'
            .($caption !== '' ? '<figcaption>'.e($caption).'</figcaption>' : '')
            .'</figure>';
    }

    private function pageList(array $attrs): string
    {
        $query = Page::published()->with('type');

        if ($typeKey = $attrs['typeKey'] ?? null) {
            $type = PageType::where('key', $typeKey)->first();
            $query->where('page_type_id', $type?->id ?? 0);
        }
        if ($tagSlug = $attrs['tag'] ?? null) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
        }

        match ($attrs['sort'] ?? 'number') {
            'title' => $query->orderBy('title'),
            'recent' => $query->latest('published_at')->latest('id'),
            default => $query->orderByRaw('number is null')->orderBy('number')->orderBy('title'),
        };

        $pages = $query->limit(min(200, max(1, (int) ($attrs['limit'] ?? 50))))->get();
        $title = trim((string) ($attrs['title'] ?? ''));

        return view('content.page-list', compact('pages', 'title'))->render();
    }

    private function pageCard(array $attrs): string
    {
        $page = $this->pages->get((int) ($attrs['pageId'] ?? 0));

        if (! $page || ! $this->visible($page)) {
            return '<div class="blk-card blk-card-missing">Запись отсутствует в архиве или изъята.</div>';
        }

        return view('content.page-card', ['page' => $page])->render();
    }

    private function footnote(array $attrs): string
    {
        $this->footnotes[] = (string) ($attrs['text'] ?? '');
        $n = count($this->footnotes);

        return '<sup class="blk-footnote" id="fnref-'.$n.'"><a href="#fn-'.$n.'" title="'.e($attrs['text'] ?? '').'">['.$n.']</a></sup>';
    }

    private function plain(array $node): string
    {
        return e(ContentAnalyzer::plainText($node));
    }

    private function suffix(string $label): string
    {
        return $label !== '' ? ' · '.$label : '';
    }

    private function visible(Page $page): bool
    {
        return $page->isPublished() || auth()->check();
    }

    public static function safeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return str_starts_with($url, '//') ? null : $url;
        }

        return preg_match('#^(https?:|mailto:)#i', $url) ? $url : null;
    }
}
