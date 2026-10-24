import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import { TableKit } from '@tiptap/extension-table';
import {
    Callout,
    Collapsible,
    DocBlock,
    Footnote,
    PageLink,
    Redacted,
    Restricted,
    createImage,
    createPageCard,
    createPageList,
} from './extensions';

const MARKS = ['bold', 'italic', 'underline', 'strike', 'code', 'redacted', 'link', 'pageLink'];

/**
 * Блочный редактор страницы (Alpine-компонент).
 *
 * Экземпляр Editor хранится в замыкании, а не в данных Alpine: прокси Alpine
 * ломает внутреннее состояние ProseMirror («mismatched transaction»).
 *
 * @param {{ lookupUrl: string, uploadUrl: string, types: Array<{key: string, name: string}>, placeholder?: string }} config
 */
export default function blockEditor(config) {
    let editor = null;

    return {
        active: {},
        inTable: false,
        insertOpen: false,
        picker: { open: false, q: '', results: [], loading: false, resolve: null },

        init() {
            const input = this.$refs.input;
            let initial = null;
            try {
                initial = input.value ? JSON.parse(input.value) : null;
            } catch {
                initial = null;
            }

            editor = new Editor({
                element: this.$refs.mount,
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [2, 3, 4] },
                        link: { openOnClick: false, autolink: true, defaultProtocol: 'https' },
                    }),
                    Placeholder.configure({ placeholder: config.placeholder ?? 'Начните писать. Блоки вставляются кнопкой «+ Блок».' }),
                    TableKit.configure({ table: { resizable: false } }),
                    Redacted,
                    PageLink,
                    Collapsible,
                    Restricted,
                    DocBlock,
                    Callout,
                    Footnote,
                    createImage({ upload: () => this.uploadImage() }),
                    createPageList({ types: config.types ?? [] }),
                    createPageCard({ pick: () => this.pickPage() }),
                ],
                content: initial,
                editorProps: {
                    attributes: { class: 'ed-content prose-archive' },
                },
                onUpdate: () => {
                    input.value = JSON.stringify(editor.getJSON());
                },
                onTransaction: () => this.refresh(),
            });

            input.value = JSON.stringify(editor.getJSON());
            this.refresh();
        },

        destroy() {
            editor?.destroy();
            editor = null;
        },

        refresh() {
            if (!editor) {
                return;
            }
            const active = {};
            for (const m of MARKS) {
                active[m] = editor.isActive(m);
            }
            for (const level of [2, 3, 4]) {
                active[`h${level}`] = editor.isActive('heading', { level });
            }
            active.bulletList = editor.isActive('bulletList');
            active.orderedList = editor.isActive('orderedList');
            active.blockquote = editor.isActive('blockquote');
            this.active = active;
            this.inTable = editor.isActive('table');
        },

        run(command, ...args) {
            if (!editor) {
                return;
            }
            const chain = editor.chain().focus();
            switch (command) {
                case 'h2':
                case 'h3':
                case 'h4':
                    chain.toggleHeading({ level: Number(command.slice(1)) }).run();
                    break;
                case 'paragraph':
                    chain.setParagraph().run();
                    break;
                case 'link':
                    this.setLink();
                    break;
                default:
                    if (typeof chain[command] === 'function') {
                        chain[command](...args).run();
                    }
            }
        },

        setLink() {
            const previous = editor.getAttributes('link').href ?? '';
            const href = window.prompt('Адрес ссылки (пусто — убрать ссылку)', previous);
            if (href === null) {
                return;
            }
            const chain = editor.chain().focus().extendMarkRange('link');
            if (href.trim() === '') {
                chain.unsetLink().run();
            } else {
                chain.setLink({ href: href.trim() }).run();
            }
        },

        async linkPage() {
            if (editor.isActive('pageLink')) {
                editor.chain().focus().extendMarkRange('pageLink').unsetMark('pageLink').run();
                return;
            }
            const { from, to, empty } = editor.state.selection;
            const page = await this.pickPage();
            if (!page) {
                return;
            }
            const mark = { type: 'pageLink', attrs: { pageId: page.id, label: page.label } };
            const chain = editor.chain().focus().setTextSelection({ from, to });
            if (empty) {
                chain.insertContent({ type: 'text', text: page.code ?? page.title, marks: [mark] }).run();
            } else {
                chain.setMark('pageLink', mark.attrs).run();
            }
        },

        insert(block) {
            this.insertOpen = false;
            const chain = editor.chain().focus();
            const wrap = (name, attrs) => {
                if (!chain.wrapIn(name, attrs).run()) {
                    editor.chain().focus().insertContent({ type: name, attrs, content: [{ type: 'paragraph' }] }).run();
                }
            };

            switch (block) {
                case 'collapsible':
                    return wrap('collapsible', { title: 'Приложение' });
                case 'restricted':
                    return wrap('restricted', { level: 3 });
                case 'docBlock':
                    return wrap('docBlock', { kind: 'Протокол' });
                case 'callout':
                    return wrap('callout', { variant: 'info' });
                case 'table':
                    return chain.insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
                case 'hr':
                    return chain.setHorizontalRule().run();
                case 'pageList':
                    return chain.insertContent({ type: 'pageList', attrs: { typeKey: config.types?.[0]?.key ?? '' } }).run();
                case 'footnote': {
                    const text = window.prompt('Текст сноски');
                    if (text) {
                        chain.insertContent({ type: 'footnote', attrs: { text } }).run();
                    }
                    return;
                }
                case 'image':
                    return this.uploadImage().then((src) => {
                        if (src) {
                            editor.chain().focus().insertContent({ type: 'image', attrs: { src } }).run();
                        }
                    });
                case 'pageCard':
                    return this.pickPage().then((page) => {
                        if (page) {
                            editor.chain().focus().insertContent({ type: 'pageCard', attrs: { pageId: page.id, label: page.label } }).run();
                        }
                    });
            }
        },

        uploadImage() {
            return new Promise((resolve) => {
                const file = document.createElement('input');
                file.type = 'file';
                file.accept = 'image/*';
                file.addEventListener('change', async () => {
                    if (!file.files?.length) {
                        return resolve(null);
                    }
                    const body = new FormData();
                    body.append('file', file.files[0]);
                    try {
                        const res = await fetch(config.uploadUrl, {
                            method: 'POST',
                            body,
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            window.alert(json.message ?? 'Не удалось загрузить файл.');
                            return resolve(null);
                        }
                        resolve(json.url);
                    } catch {
                        window.alert('Не удалось загрузить файл.');
                        resolve(null);
                    }
                });
                file.click();
            });
        },

        /** Открывает окно поиска страниц и ждёт выбора. */
        pickPage() {
            return new Promise((resolve) => {
                this.picker.resolve = resolve;
                this.picker.q = '';
                this.picker.open = true;
                this.searchPages();
                // Ждём, пока x-show покажет окно, иначе фокус останется в редакторе.
                setTimeout(() => this.$refs.pickerInput?.focus(), 50);
            });
        },

        async searchPages() {
            this.picker.loading = true;
            const url = new URL(config.lookupUrl, window.location.origin);
            url.searchParams.set('q', this.picker.q);
            const res = await fetch(url, { headers: { Accept: 'application/json' } });
            this.picker.results = res.ok ? await res.json() : [];
            this.picker.loading = false;
        },

        choosePage(page) {
            this.picker.open = false;
            this.picker.resolve?.(page);
            this.picker.resolve = null;
        },

        closePicker() {
            this.choosePage(null);
        },
    };
}
