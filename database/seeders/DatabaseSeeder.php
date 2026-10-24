<?php

namespace Database\Seeders;

use App\Content\PageWriter;
use App\Models\Page;
use App\Models\PageType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@bno.local'],
            ['name' => 'Регистратор', 'password' => Hash::make('archive-demo'), 'role' => 'admin'],
        );

        $types = $this->types();
        $this->demoPages($types, $admin);
    }

    /** @return array<string, PageType> */
    private function types(): array
    {
        $definitions = [
            [
                'key' => 'object', 'name' => 'Объект', 'name_plural' => 'Объекты', 'url_prefix' => 'obj',
                'layout' => 'object', 'numbered' => true, 'number_format' => 'БНО-{n:3}', 'sort' => 10,
                'description' => 'Предметы, места, существа и явления, находящиеся под надзором Бюро.',
                'fields' => [
                    ['key' => 'danger_class', 'label' => 'Класс опасности', 'type' => 'select', 'header' => true, 'list' => true, 'required' => true,
                        'options' => ['Инертный', 'Нестабильный', 'Опасный', 'Критический', 'Нейтрализован']],
                    ['key' => 'access_level', 'label' => 'Уровень допуска', 'type' => 'select', 'header' => true,
                        'options' => ['1', '2', '3', '4', '5']],
                    ['key' => 'containment', 'label' => 'Статус', 'type' => 'select', 'header' => true, 'list' => true,
                        'options' => ['Содержится', 'Под наблюдением', 'Утерян', 'Ликвидирован']],
                    ['key' => 'location', 'label' => 'Место содержания', 'type' => 'text', 'header' => true],
                    ['key' => 'discovered', 'label' => 'Дата обнаружения', 'type' => 'date'],
                ],
            ],
            [
                'key' => 'document', 'name' => 'Документ', 'name_plural' => 'Документы', 'url_prefix' => 'doc',
                'layout' => 'document', 'numbered' => false, 'sort' => 20,
                'description' => 'Приказы, протоколы, рапорты, акты и прочая служебная переписка Бюро.',
                'fields' => [
                    ['key' => 'secrecy', 'label' => 'Гриф', 'type' => 'select', 'header' => true,
                        'options' => ['Для служебного пользования', 'Секретно', 'Совершенно секретно', 'Особой важности']],
                    ['key' => 'doc_kind', 'label' => 'Вид документа', 'type' => 'select', 'header' => true, 'list' => true,
                        'options' => ['Приказ', 'Протокол', 'Рапорт', 'Служебная записка', 'Акт', 'Расшифровка']],
                    ['key' => 'doc_number', 'label' => 'Номер', 'type' => 'text', 'header' => true, 'list' => true],
                    ['key' => 'doc_date', 'label' => 'Дата', 'type' => 'date', 'header' => true, 'list' => true],
                    ['key' => 'issued_by', 'label' => 'Составил', 'type' => 'text', 'header' => true],
                    ['key' => 'objects', 'label' => 'Объекты', 'type' => 'pages', 'page_type' => 'object', 'header' => true],
                ],
            ],
            [
                'key' => 'article', 'name' => 'Статья', 'name_plural' => 'Статьи', 'url_prefix' => 'stati',
                'layout' => 'article', 'numbered' => false, 'sort' => 30,
                'description' => 'Рассказы, очерки и свидетельства из мира Бюро.',
                'fields' => [
                    ['key' => 'author', 'label' => 'Автор', 'type' => 'text', 'header' => true, 'list' => true],
                    ['key' => 'cycle', 'label' => 'Цикл', 'type' => 'text', 'header' => true, 'list' => true],
                    ['key' => 'objects', 'label' => 'Объекты', 'type' => 'pages', 'page_type' => 'object', 'header' => true],
                ],
            ],
            [
                'key' => 'info', 'name' => 'Справка', 'name_plural' => 'Справочник', 'url_prefix' => 'info',
                'layout' => 'info', 'numbered' => false, 'sort' => 40,
                'description' => 'О Бюро, классификации, правила и оглавления.',
                'fields' => [],
            ],
        ];

        $types = [];
        foreach ($definitions as $d) {
            $types[$d['key']] = PageType::updateOrCreate(['key' => $d['key']], $d);
        }

        return $types;
    }

    private function demoPages(array $types, User $admin): void
    {
        if (Page::exists()) {
            return;
        }

        $writer = new PageWriter;
        $save = fn (string $type, array $data, ?Page $page = null) => $writer->save(
            $page ?? new Page, $types[$type], $data + ['status' => 'published'], $admin, 'Первичное заполнение',
        );

        $mirror = $save('object', [
            'title' => 'Зеркало в прихожей',
            'number' => 1,
            'summary' => 'Трюмо 1960-х годов, отражение в котором отстаёт от оригинала на 4–6 секунд.',
            'tags' => 'зеркала, предметы быта, хронологические',
            'fields' => ['danger_class' => 'Нестабильный', 'access_level' => '3', 'containment' => 'Содержится',
                'location' => 'Хранилище №2, ячейка 14', 'discovered' => '1987-03-11'],
        ]);

        $order = $save('document', [
            'title' => 'О порядке обращения с отражающими поверхностями',
            'summary' => 'Приказ по хранилищу №2 после инцидента 14.02.1991.',
            'tags' => 'зеркала, приказы',
            'fields' => ['secrecy' => 'Секретно', 'doc_kind' => 'Приказ', 'doc_number' => '17-ХР',
                'doc_date' => '1991-02-16', 'issued_by' => 'Начальник хранилища №2 т. В. ███████', 'objects' => [$mirror->id]],
            'content' => self::doc(
                self::p('В связи с инцидентом в ячейке 14 хранилища №2 ', self::t('ПРИКАЗЫВАЮ', ['bold']), ':'),
                self::ol(
                    'Запретить персоналу находиться перед объектом более пяти секунд.',
                    'Закрыть все отражающие поверхности в радиусе 10 метров светонепроницаемой тканью.',
                    'Сотрудника, находившегося в ячейке 14 в момент инцидента, отстранить от работы до заключения медицинской комиссии.',
                ),
                self::p('Контроль за исполнением приказа оставляю за собой.'),
            ),
        ]);

        $save('object', [
            'title' => 'Зеркало в прихожей',
            'number' => 1,
            'summary' => $mirror->summary,
            'tags' => 'зеркала, предметы быта, хронологические',
            'fields' => $mirror->fields,
            'content' => self::doc(
                self::h(2, 'Условия содержания'),
                self::p('Объект хранится в ячейке 14 хранилища №2, зеркальная поверхность закрыта плотной тканью. Персоналу запрещено смотреть в объект дольше пяти секунд (см. ',
                    self::t('приказ №17-ХР', [['type' => 'pageLink', 'attrs' => ['pageId' => $order->id]]]), ').'),
                self::callout('warning', 'Внимание', self::p('Если отражение в объекте перестаёт повторять движения наблюдателя, наблюдателю следует немедленно покинуть ячейку, не оборачиваясь.')),
                self::h(2, 'Описание'),
                self::p('Объект — трюмо из полированного дерева с тремя створками, производство мебельной фабрики ',
                    self::t('города Ч███████', ['redacted']),
                    ', около 1964 года. Отражение в центральной створке отстаёт от реальности на 4–6 секунд',
                    self::footnote('По результатам 212 замеров. Задержка растёт ночью и в присутствии более чем двух наблюдателей.'),
                    '. Боковые створки аномальных свойств не проявляют.'),
                self::p('При длительном наблюдении отражение начинает действовать самостоятельно. Зафиксированы случаи, когда отражение ',
                    self::t('покидало кадр', ['italic']), ' раньше наблюдателя.'),
                self::table(
                    ['Замер', 'Наблюдателей', 'Задержка, с'],
                    ['№ 12', '1', '4,1'],
                    ['№ 57', '2', '4,8'],
                    ['№ 203', '3', '9,6'],
                ),
                self::collapsible('Приложение 1. Протокол испытания №4', self::docBlock('Протокол испытания', '№ 4', 'Хранилище №2 · 14.02.1991',
                    self::p(self::t('Испытуемый:', ['bold']), ' сотрудник Д-11.'),
                    self::p(self::t('Ход испытания:', ['bold']), ' испытуемому поручено стоять перед объектом с поднятой правой рукой.'),
                    self::p('00:00 — отражение повторяет позу с задержкой 4 с.'),
                    self::p('01:30 — отражение опускает руку. Испытуемый руку не опускал.'),
                    self::p('01:42 — отражение ', self::t('поворачивается к испытуемому спиной', ['redacted']), '.'),
                    self::p('01:43 — испытание прекращено.'),
                )),
                self::restricted(4, 'Отдел хронологии', self::p('По оценке отдела хронологии, задержка отражения не является постоянной величиной. При текущей скорости роста отражение опередит оригинал примерно в ',
                    self::t('2031', ['redacted']), ' году. Последствия не прогнозируются.')),
            ),
        ], $mirror);

        $save('object', [
            'title' => 'Подъезд, который не заканчивается',
            'number' => 2,
            'summary' => 'Пятиэтажный жилой дом, в одном из подъездов которого бывает больше пяти этажей.',
            'tags' => 'здания, пространственные',
            'fields' => ['danger_class' => 'Опасный', 'access_level' => '2', 'containment' => 'Под наблюдением',
                'location' => 'г. ███████, ул. Строителей, д. 9', 'discovered' => '1979-11-02'],
            'content' => self::doc(
                self::h(2, 'Условия содержания'),
                self::p('Жильцы подъезда №3 расселены под предлогом аварийного состояния здания. Вход в подъезд опечатан. Наблюдение ведётся с поста в квартире 41 соседнего дома.'),
                self::h(2, 'Описание'),
                self::p('В тёмное время суток лестница подъезда №3 продолжается выше пятого этажа. Наибольшее зафиксированное число этажей — 23. Двери квартир на дополнительных этажах обиты дерматином и пронумерованы с повторами.'),
            ),
        ]);

        $save('article', [
            'title' => 'Ночная смена',
            'summary' => 'Рассказ охранника хранилища №2 о том, почему зеркала там завешивают.',
            'tags' => 'зеркала, рассказы',
            'fields' => ['author' => 'Демо-автор', 'cycle' => 'Хранилище №2', 'objects' => [$mirror->id]],
            'content' => self::doc(
                self::p('В хранилище №2 я проработал одиннадцать лет, и за всё это время ни разу не видел своего отражения на работе. Не потому что не хотел — потому что нельзя.'),
                self::p('Ткань на ', self::t('БНО-001', [['type' => 'pageLink', 'attrs' => ['pageId' => $mirror->id]]]), ' меняли раз в квартал. Двое держат, третий снимает старую. Смотреть в пол. Считать до пяти.'),
                self::quote(self::p('Главное — не оборачивайся, если оно уйдёт первым.')),
                self::p('Так мне сказал Ковалёв в первую же смену. Тогда я ещё смеялся.'),
            ),
        ]);

        $save('info', [
            'title' => 'О Бюро',
            'slug' => 'about',
            'summary' => 'Что такое Бюро нетипичных объектов и как устроен этот архив.',
            'content' => self::doc(
                self::p('Бюро нетипичных объектов — ведомство, которое занимается выявлением, изучением и удержанием аномальных предметов, мест и явлений.'),
                self::h(2, 'Классы опасности'),
                self::table(
                    ['Класс', 'Значение'],
                    ['Инертный', 'Не проявляет активности без внешнего воздействия.'],
                    ['Нестабильный', 'Свойства меняются, поведение предсказуемо лишь отчасти.'],
                    ['Опасный', 'Представляет угрозу жизни персонала.'],
                    ['Критический', 'Угроза выходит за пределы места содержания.'],
                    ['Нейтрализован', 'Аномальные свойства утрачены или подавлены.'],
                ),
                self::h(2, 'Реестр объектов'),
                ['type' => 'pageList', 'attrs' => ['typeKey' => 'object', 'sort' => 'number', 'limit' => 50, 'title' => '', 'tag' => '']],
            ),
        ]);
    }

    // ===== Короткие конструкторы JSON-документа редактора =====

    private static function doc(array ...$blocks): array
    {
        return ['type' => 'doc', 'content' => $blocks];
    }

    private static function inline(array $parts): array
    {
        return array_map(fn ($part) => is_string($part) ? ['type' => 'text', 'text' => $part] : $part, $parts);
    }

    private static function p(string|array ...$parts): array
    {
        return ['type' => 'paragraph', 'content' => self::inline($parts)];
    }

    private static function h(int $level, string $text): array
    {
        return ['type' => 'heading', 'attrs' => ['level' => $level], 'content' => [['type' => 'text', 'text' => $text]]];
    }

    private static function t(string $text, array $marks): array
    {
        return ['type' => 'text', 'text' => $text, 'marks' => array_map(fn ($m) => is_string($m) ? ['type' => $m] : $m, $marks)];
    }

    private static function footnote(string $text): array
    {
        return ['type' => 'footnote', 'attrs' => ['text' => $text]];
    }

    private static function ol(string ...$items): array
    {
        return ['type' => 'orderedList', 'attrs' => ['start' => 1], 'content' => array_map(
            fn ($item) => ['type' => 'listItem', 'content' => [self::p($item)]], $items,
        )];
    }

    private static function quote(array ...$blocks): array
    {
        return ['type' => 'blockquote', 'content' => $blocks];
    }

    private static function table(array $header, array ...$rows): array
    {
        $row = fn (array $cells, string $type) => ['type' => 'tableRow', 'content' => array_map(
            fn ($c) => ['type' => $type, 'content' => [self::p($c)]], $cells,
        )];

        return ['type' => 'table', 'content' => [$row($header, 'tableHeader'), ...array_map(fn ($r) => $row($r, 'tableCell'), $rows)]];
    }

    private static function callout(string $variant, string $title, array ...$blocks): array
    {
        return ['type' => 'callout', 'attrs' => ['variant' => $variant, 'title' => $title], 'content' => $blocks];
    }

    private static function collapsible(string $title, array ...$blocks): array
    {
        return ['type' => 'collapsible', 'attrs' => ['title' => $title, 'open' => false], 'content' => $blocks];
    }

    private static function docBlock(string $kind, string $title, string $meta, array ...$blocks): array
    {
        return ['type' => 'docBlock', 'attrs' => compact('kind', 'title', 'meta'), 'content' => $blocks];
    }

    private static function restricted(int $level, string $label, array ...$blocks): array
    {
        return ['type' => 'restricted', 'attrs' => compact('level', 'label'), 'content' => $blocks];
    }
}
