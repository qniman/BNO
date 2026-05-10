import Alpine from 'alpinejs';
import htmx from 'htmx.org';
import registryMarkdownEditor from './registryMarkdownEditor';

window.htmx = htmx;

Alpine.data('registryMarkdownEditor', registryMarkdownEditor);

Alpine.start();
