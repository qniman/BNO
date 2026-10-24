/**
 * Представление блока в редакторе: шапка с настройками (заголовок, уровень допуска и т.п.)
 * и, для блоков-контейнеров, редактируемое тело.
 *
 * Поля шапки меняют атрибуты узла через транзакцию; события внутри шапки
 * ProseMirror не обрабатывает (stopEvent), чтобы не терять фокус в input.
 *
 * @param {{
 *   label: string,
 *   className: string,
 *   fields?: Array<{ name: string, type?: 'text'|'number'|'select'|'checkbox', placeholder?: string, options?: Array<[string,string]>|(() => Array<[string,string]>), width?: string }>,
 *   container?: boolean,
 *   render?: (body: HTMLElement, node: any, api: { setAttrs: (a: object) => void }) => void,
 * }} spec
 */
export function blockView(spec) {
    return ({ node, getPos, editor }) => {
        let current = node;

        const dom = document.createElement('div');
        dom.className = `ed-block ${spec.className}`;

        const head = document.createElement('div');
        head.className = 'ed-block-head';
        head.contentEditable = 'false';

        const tag = document.createElement('span');
        tag.className = 'ed-block-label';
        tag.textContent = spec.label;
        head.append(tag);

        const setAttrs = (attrs) => {
            const pos = getPos();
            if (typeof pos !== 'number') {
                return;
            }
            const tr = editor.view.state.tr.setNodeMarkup(pos, undefined, { ...current.attrs, ...attrs });
            editor.view.dispatch(tr);
        };

        const inputs = {};
        for (const field of spec.fields ?? []) {
            let input;
            if (field.type === 'select') {
                input = document.createElement('select');
                const options = typeof field.options === 'function' ? field.options() : field.options;
                for (const [value, text] of options ?? []) {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = text;
                    input.append(opt);
                }
                input.addEventListener('change', () => setAttrs({ [field.name]: input.value }));
            } else if (field.type === 'checkbox') {
                const wrap = document.createElement('label');
                wrap.className = 'ed-check';
                input = document.createElement('input');
                input.type = 'checkbox';
                wrap.append(input, document.createTextNode(field.placeholder ?? ''));
                input.addEventListener('change', () => setAttrs({ [field.name]: input.checked }));
                head.append(wrap);
                inputs[field.name] = input;
                continue;
            } else {
                input = document.createElement('input');
                input.type = field.type === 'number' ? 'number' : 'text';
                input.placeholder = field.placeholder ?? '';
                input.addEventListener('input', () => {
                    const v = field.type === 'number' ? Number(input.value) || null : input.value;
                    setAttrs({ [field.name]: v });
                });
            }
            if (field.width) {
                input.style.width = field.width;
            }
            input.className = 'ed-input';
            head.append(input);
            inputs[field.name] = input;
        }

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'ed-block-remove';
        remove.title = 'Удалить блок';
        remove.textContent = '×';
        remove.addEventListener('click', () => {
            const pos = getPos();
            if (typeof pos === 'number') {
                editor.view.dispatch(editor.view.state.tr.delete(pos, pos + current.nodeSize));
            }
        });
        head.append(remove);

        dom.append(head);

        let contentDOM = null;
        let body = null;
        if (spec.container) {
            contentDOM = document.createElement('div');
            contentDOM.className = 'ed-block-body';
            dom.append(contentDOM);
        } else {
            body = document.createElement('div');
            body.className = 'ed-block-preview';
            body.contentEditable = 'false';
            dom.append(body);
        }

        const syncInputs = () => {
            for (const [name, input] of Object.entries(inputs)) {
                if (document.activeElement === input) {
                    continue;
                }
                const value = current.attrs[name];
                if (input.type === 'checkbox') {
                    input.checked = !!value;
                } else {
                    input.value = value ?? '';
                }
            }
            if (body && spec.render) {
                spec.render(body, current, { setAttrs });
            }
        };
        syncInputs();

        return {
            dom,
            contentDOM,
            update(updated) {
                if (updated.type !== current.type) {
                    return false;
                }
                current = updated;
                syncInputs();
                return true;
            },
            stopEvent(event) {
                return head.contains(event.target) || (body !== null && body.contains(event.target));
            },
            ignoreMutation(mutation) {
                if (!contentDOM) {
                    return true;
                }
                if (mutation.type === 'selection') {
                    return false;
                }
                return !contentDOM.contains(mutation.target);
            },
        };
    };
}
