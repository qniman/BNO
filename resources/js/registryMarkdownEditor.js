import 'prosemirror-view/style/prosemirror.css';

import { Editor } from '@tiptap/core';
import { Markdown } from '@tiptap/markdown';
import Placeholder from '@tiptap/extension-placeholder';
import StarterKit from '@tiptap/starter-kit';

/**
 * Alpine.js + TipTap (Markdown). Панель — отдельные слушатели.
 *
 * Не вешать синхронизацию textarea на каждый `update`: RAF + getMarkdown на фоне набора
 * может пересечься с кликом по панели; тогда документ успевает измениться между созданием
 * `tr` и `dispatch` → RangeError «Applying a mismatched transaction» (tr.before ≠ state.doc).
 *
 * Синк: blur редактора, submit формы, после команды панели.
 * Команды панели — в setTimeout(0), после завершения цикла событий браузера/PM.
 *
 * Не использовать commands.focus() (delayedFocus + scrollIntoView в rAF).
 *
 * @param {{ placeholder?: string, minHeight?: number }} config
 */
export default function registryMarkdownEditor(config = {}) {
    const placeholder = config.placeholder ?? '';
    const minHeight = Number(config.minHeight) > 0 ? Number(config.minHeight) : 220;

    return {
        editor: null,
        _form: null,
        _onSubmit: null,
        _toolbarAbort: null,
        _toolbarTimer: null,
        _toolbarBusy: false,

        init() {
            const ta = this.$refs.mdSource;
            const mount = this.$refs.mdMount;
            /** @type {HTMLElement | null} */
            const inner = mount?.querySelector('[data-registry-tiptap-root]');
            const toolbar = this.$refs.mdToolbar;

            if (!ta || !mount || !inner || this.editor) {
                return;
            }

            mount.style.minHeight = `${minHeight}px`;

            const initial = ta.value ?? '';

            const sync = () => {
                if (!this.editor || this.editor.isDestroyed || this._toolbarBusy) {
                    return;
                }
                ta.value = this.editor.getMarkdown();
            };

            this.editor = new Editor({
                element: { mount: inner },
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [2, 3] },
                    }),
                    Markdown,
                    Placeholder.configure({
                        placeholder: placeholder || 'Текст…',
                        emptyEditorClass: 'is-editor-empty',
                    }),
                ],
                content: initial,
                contentType: 'markdown',
                injectCSS: false,
                editorProps: {
                    attributes: {
                        class:
                            'registry-prosemirror focus:outline-none min-h-[inherit] px-2 py-2 text-sm leading-relaxed text-bno-archive doc-serif normal-case',
                    },
                },
            });

            this.editor.on('blur', () => {
                sync();
            });

            queueMicrotask(() => sync());

            this._form = ta.closest('form');
            this._onSubmit = sync;
            this._form?.addEventListener('submit', this._onSubmit, true);

            if (!toolbar) {
                return;
            }

            this._toolbarAbort = new AbortController();
            const { signal } = this._toolbarAbort;

            const runToolbarCommand = (cmd) => {
                const ed = this.editor;
                if (!ed || ed.isDestroyed || !cmd) {
                    return;
                }

                if (this._toolbarTimer != null) {
                    clearTimeout(this._toolbarTimer);
                    this._toolbarTimer = null;
                    this._toolbarBusy = false;
                }

                this._toolbarBusy = true;

                this._toolbarTimer = window.setTimeout(() => {
                    this._toolbarTimer = null;

                    const editor = this.editor;
                    if (!editor || editor.isDestroyed || !cmd) {
                        this._toolbarBusy = false;
                        return;
                    }

                    try {
                        switch (cmd) {
                            case 'bold':
                                editor.commands.toggleBold();
                                break;
                            case 'italic':
                                editor.commands.toggleItalic();
                                break;
                            case 'h2':
                                editor.commands.toggleHeading({ level: 2 });
                                break;
                            case 'h3':
                                editor.commands.toggleHeading({ level: 3 });
                                break;
                            case 'bullet':
                                editor.commands.toggleBulletList();
                                break;
                            case 'ordered':
                                editor.commands.toggleOrderedList();
                                break;
                            case 'quote':
                                editor.commands.toggleBlockquote();
                                break;
                            case 'code':
                                editor.commands.toggleCodeBlock();
                                break;
                            case 'undo':
                                editor.commands.undo();
                                break;
                            case 'redo':
                                editor.commands.redo();
                                break;
                            default:
                                break;
                        }
                    } catch (e) {
                        console.warn('[registryMarkdownEditor]', e);
                    } finally {
                        queueMicrotask(() => {
                            this._toolbarBusy = false;
                            sync();
                        });
                    }
                }, 0);
            };

            toolbar.addEventListener(
                'mousedown',
                (e) => {
                    const btn = e.target.closest('[data-registry-cmd]');
                    if (!btn || !toolbar.contains(btn)) {
                        return;
                    }
                    e.preventDefault();
                },
                { capture: true, signal },
            );

            toolbar.addEventListener(
                'click',
                (e) => {
                    const btn = e.target.closest('[data-registry-cmd]');
                    if (!btn || !toolbar.contains(btn)) {
                        return;
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    runToolbarCommand(btn.getAttribute('data-registry-cmd'));
                },
                { capture: true, signal },
            );

            toolbar.addEventListener(
                'keydown',
                (e) => {
                    if (e.key !== 'Enter' && e.key !== ' ') {
                        return;
                    }
                    const btn = e.target.closest('[data-registry-cmd]');
                    if (!btn || !toolbar.contains(btn)) {
                        return;
                    }
                    e.preventDefault();
                    runToolbarCommand(btn.getAttribute('data-registry-cmd'));
                },
                { signal },
            );
        },

        destroy() {
            if (this._toolbarTimer != null) {
                clearTimeout(this._toolbarTimer);
                this._toolbarTimer = null;
            }
            if (this._toolbarAbort) {
                this._toolbarAbort.abort();
                this._toolbarAbort = null;
            }
            if (this._form && this._onSubmit) {
                this._form.removeEventListener('submit', this._onSubmit, true);
            }
            this.editor?.destroy();
            this.editor = null;
            this._toolbarBusy = false;
        },
    };
}
