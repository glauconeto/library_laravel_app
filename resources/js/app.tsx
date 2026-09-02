import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { route } from 'ziggy-js';

declare const Ziggy: {
    url: string;
    port: number | null;
    defaults: Record<string, unknown>;
    routes: Record<string, unknown>;
};

declare const window: {
    route: (name: string, params?: Record<string, unknown>, absolute?: boolean) => string;
};

window.route = (name: string, params?: Record<string, unknown>, absolute?: boolean) =>
    route(name, params, absolute, Ziggy);

createInertiaApp({
    resolve: (name) => {
        const pages = (import.meta as any).glob('./pages/**/*.tsx', { eager: true });
        return pages[`./pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});