import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { route } from 'ziggy-js';

window.route = (name, params, absolute) => route(name, params, absolute, Ziggy);

createInertiaApp({
    resolve: (name) => {
        const pages = (import.meta as any).glob('./pages/**/*.tsx', { eager: true });
        return pages[`./pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
