import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Редактор нужен только в админке — грузим его отдельным чанком.
if (document.querySelector('[data-block-editor]')) {
    const { default: blockEditor } = await import('./editor/blockEditor');
    Alpine.data('blockEditor', blockEditor);
}

Alpine.data('fieldsEditor', (initial) => ({
    fields: (initial ?? []).map((f) => ({ ...f, options: Array.isArray(f.options) ? f.options.join('\n') : (f.options ?? '') })),
    add() {
        this.fields.push({ key: '', label: '', type: 'text', options: '', page_type: '', required: false, header: true, list: false, help: '' });
    },
    remove(i) {
        this.fields.splice(i, 1);
    },
    move(i, dir) {
        const j = i + dir;
        if (j < 0 || j >= this.fields.length) {
            return;
        }
        [this.fields[i], this.fields[j]] = [this.fields[j], this.fields[i]];
    },
    get json() {
        return JSON.stringify(this.fields);
    },
}));

/** Выбор страниц для полей «Ссылка на страницу». */
Alpine.data('pagePicker', ({ url, type, multiple, selected }) => ({
    selected: selected ?? [],
    q: '',
    results: [],
    open: false,
    async search() {
        const u = new URL(url, window.location.origin);
        u.searchParams.set('q', this.q);
        if (type) {
            u.searchParams.set('type', type);
        }
        const res = await fetch(u, { headers: { Accept: 'application/json' } });
        this.results = res.ok ? await res.json() : [];
        this.open = true;
    },
    add(page) {
        if (!this.selected.some((p) => p.id === page.id)) {
            this.selected = multiple ? [...this.selected, page] : [page];
        }
        this.q = '';
        this.open = false;
    },
    remove(id) {
        this.selected = this.selected.filter((p) => p.id !== id);
    },
}));

Alpine.start();
