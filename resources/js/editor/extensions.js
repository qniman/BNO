import { Mark, Node, mergeAttributes } from '@tiptap/core';
import { blockView } from './nodeView';

/** Атрибут, хранящийся в data-*, чтобы блоки переживали копирование/вставку. */
const dataAttr = (name, fallback = null, cast = (v) => v) => {
    const html = `data-${name.replace(/[A-Z]/g, (c) => `-${c.toLowerCase()}`)}`;
    return {
        default: fallback,
        parseHTML: (el) => {
            const v = el.getAttribute(html);
            return v === null ? fallback : cast(v);
        },
        renderHTML: (attrs) => (attrs[name] === null || attrs[name] === undefined ? {} : { [html]: attrs[name] }),
    };
};

const toNumber = (v) => (v === '' ? null : Number(v));
const toBool = (v) => v === 'true';

/** Цензура: в публичной версии текст заменяется на █████. */
export const Redacted = Mark.create({
    name: 'redacted',
    excludes: '_',
    parseHTML: () => [{ tag: 'span[data-redacted]' }],
    renderHTML: ({ HTMLAttributes }) => ['span', mergeAttributes(HTMLAttributes, { 'data-redacted': '', class: 'ed-redacted' }), 0],
    addCommands() {
        return {
            toggleRedacted:
                () =>
                ({ commands }) =>
                    commands.toggleMark(this.name),
        };
    },
    addKeyboardShortcuts() {
        return { 'Mod-Shift-h': () => this.editor.commands.toggleRedacted() };
    },
});

/** Ссылка на страницу архива по id — не ломается при смене адреса страницы. */
export const PageLink = Mark.create({
    name: 'pageLink',
    priority: 1001,
    inclusive: false,
    addAttributes: () => ({
        pageId: dataAttr('pageId', null, toNumber),
        label: dataAttr('label'),
    }),
    parseHTML: () => [{ tag: 'a[data-page-id]' }],
    renderHTML: ({ HTMLAttributes }) => ['a', mergeAttributes(HTMLAttributes, { class: 'ed-page-link', title: HTMLAttributes['data-label'] ?? '' }), 0],
});

const containerNode = ({ name, label, className, attrs, fields }) =>
    Node.create({
        name,
        group: 'block',
        content: 'block+',
        defining: true,
        addAttributes: () => attrs,
        parseHTML: () => [{ tag: `div[data-type="${name}"]` }],
        renderHTML: ({ HTMLAttributes }) => ['div', mergeAttributes(HTMLAttributes, { 'data-type': name }), 0],
        addNodeView: () => blockView({ label, className, fields, container: true }),
    });

export const Collapsible = containerNode({
    name: 'collapsible',
    label: 'Раскрывающийся блок',
    className: 'ed-collapsible',
    attrs: {
        title: dataAttr('title', 'Приложение'),
        open: dataAttr('open', false, toBool),
    },
    fields: [
        { name: 'title', placeholder: 'Заголовок', width: '18rem' },
        { name: 'open', type: 'checkbox', placeholder: 'раскрыт' },
    ],
});

export const Restricted = containerNode({
    name: 'restricted',
    label: 'Ограниченный доступ',
    className: 'ed-restricted',
    attrs: {
        level: dataAttr('level', 3, toNumber),
        label: dataAttr('label', ''),
    },
    fields: [
        { name: 'level', type: 'select', options: [1, 2, 3, 4, 5, 6].map((n) => [String(n), `Уровень ${n}`]) },
        { name: 'label', placeholder: 'Пометка (необязательно)', width: '16rem' },
    ],
});

export const DocBlock = containerNode({
    name: 'docBlock',
    label: 'Документ',
    className: 'ed-doc',
    attrs: {
        kind: dataAttr('kind', 'Протокол'),
        title: dataAttr('title', ''),
        meta: dataAttr('meta', ''),
    },
    fields: [
        { name: 'kind', placeholder: 'Вид: протокол, рапорт…', width: '10rem' },
        { name: 'title', placeholder: 'Заголовок', width: '14rem' },
        { name: 'meta', placeholder: 'Реквизиты: дата, № …', width: '14rem' },
    ],
});

export const Callout = containerNode({
    name: 'callout',
    label: 'Врезка',
    className: 'ed-callout',
    attrs: {
        variant: dataAttr('variant', 'info'),
        title: dataAttr('title', ''),
    },
    fields: [
        {
            name: 'variant',
            type: 'select',
            options: [
                ['info', 'Справка'],
                ['warning', 'Предупреждение'],
                ['danger', 'Опасность'],
            ],
        },
        { name: 'title', placeholder: 'Заголовок', width: '18rem' },
    ],
});

const atomNode = ({ name, label, className, attrs, fields, render }) =>
    Node.create({
        name,
        group: 'block',
        atom: true,
        draggable: true,
        addAttributes: () => attrs,
        parseHTML: () => [{ tag: `div[data-type="${name}"]` }],
        renderHTML: ({ HTMLAttributes }) => ['div', mergeAttributes(HTMLAttributes, { 'data-type': name })],
        addNodeView: () => blockView({ label, className, fields, render }),
    });

export const createImage = ({ upload }) =>
    atomNode({
        name: 'image',
        label: 'Изображение',
        className: 'ed-image',
        attrs: {
            src: dataAttr('src', ''),
            alt: dataAttr('alt', ''),
            caption: dataAttr('caption', ''),
            size: dataAttr('size', 'full'),
            align: dataAttr('align', 'center'),
        },
        fields: [
            { name: 'caption', placeholder: 'Подпись', width: '18rem' },
            {
                name: 'size',
                type: 'select',
                options: [
                    ['full', 'Во всю ширину'],
                    ['half', 'Половина'],
                    ['small', 'Маленькое'],
                ],
            },
            {
                name: 'align',
                type: 'select',
                options: [
                    ['center', 'По центру'],
                    ['left', 'Слева'],
                    ['right', 'Справа'],
                ],
            },
        ],
        render(body, node, { setAttrs }) {
            body.replaceChildren();
            if (node.attrs.src) {
                const img = document.createElement('img');
                img.src = node.attrs.src;
                body.append(img);
            }
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ed-btn';
            btn.textContent = node.attrs.src ? 'Заменить файл' : 'Загрузить файл';
            btn.addEventListener('click', async () => {
                const src = await upload();
                if (src) {
                    setAttrs({ src });
                }
            });
            body.append(btn);
        },
    });

export const createPageList = ({ types }) =>
    atomNode({
        name: 'pageList',
        label: 'Автосписок страниц',
        className: 'ed-pagelist',
        attrs: {
            title: dataAttr('title', ''),
            typeKey: dataAttr('typeKey', ''),
            tag: dataAttr('tag', ''),
            sort: dataAttr('sort', 'number'),
            limit: dataAttr('limit', 50, toNumber),
        },
        fields: [
            { name: 'title', placeholder: 'Заголовок списка', width: '12rem' },
            { name: 'typeKey', type: 'select', options: () => [['', 'Все типы'], ...types.map((t) => [t.key, t.name])] },
            { name: 'tag', placeholder: 'тег (адрес)', width: '8rem' },
            {
                name: 'sort',
                type: 'select',
                options: [
                    ['number', 'По номеру'],
                    ['title', 'По названию'],
                    ['recent', 'Новые сверху'],
                ],
            },
            { name: 'limit', type: 'number', placeholder: 'макс.', width: '4.5rem' },
        ],
        render(body, node) {
            const type = types.find((t) => t.key === node.attrs.typeKey);
            body.textContent = `Здесь будет список: ${type ? type.name.toLowerCase() : 'все страницы'}${node.attrs.tag ? `, тег «${node.attrs.tag}»` : ''}.`;
        },
    });

export const createPageCard = ({ pick }) =>
    atomNode({
        name: 'pageCard',
        label: 'Карточка страницы',
        className: 'ed-pagecard',
        attrs: {
            pageId: dataAttr('pageId', null, toNumber),
            label: dataAttr('label', ''),
        },
        render(body, node, { setAttrs }) {
            body.replaceChildren();
            const text = document.createElement('span');
            text.textContent = node.attrs.label || 'Страница не выбрана';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ed-btn';
            btn.textContent = 'Выбрать';
            btn.addEventListener('click', async () => {
                const page = await pick();
                if (page) {
                    setAttrs({ pageId: page.id, label: page.label });
                }
            });
            body.append(text, btn);
        },
    });

/** Сноска: инлайновый значок, текст правится по клику. Список сносок собирается в конце страницы. */
export const Footnote = Node.create({
    name: 'footnote',
    group: 'inline',
    inline: true,
    atom: true,
    addAttributes: () => ({ text: dataAttr('text', '') }),
    parseHTML: () => [{ tag: 'sup[data-type="footnote"]' }],
    renderHTML: ({ HTMLAttributes }) => ['sup', mergeAttributes(HTMLAttributes, { 'data-type': 'footnote' }), '*'],
    addNodeView:
        () =>
        ({ node, getPos, editor }) => {
            let current = node;
            const dom = document.createElement('sup');
            dom.className = 'ed-footnote';
            const paint = () => {
                dom.textContent = '[сноска]';
                dom.title = current.attrs.text || 'Пустая сноска — нажмите, чтобы ввести текст';
            };
            paint();
            dom.addEventListener('click', () => {
                const text = window.prompt('Текст сноски', current.attrs.text ?? '');
                const pos = getPos();
                if (text === null || typeof pos !== 'number') {
                    return;
                }
                editor.view.dispatch(editor.view.state.tr.setNodeMarkup(pos, undefined, { text }));
            });
            return {
                dom,
                update(updated) {
                    if (updated.type !== current.type) {
                        return false;
                    }
                    current = updated;
                    paint();
                    return true;
                },
                ignoreMutation: () => true,
            };
        },
});
