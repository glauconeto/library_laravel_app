import './bootstrap';
import React from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { route } from 'ziggy-js';
import { Ziggy } from './ziggy';

window.route = (name: string, params?: Record<string, unknown>, absolute?: boolean) =>
    route(name, params, absolute, Ziggy);

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx', { eager: true });
        return pages[`./pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});