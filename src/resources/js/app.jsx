import './bootstrap';
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

createInertiaApp({
    title: (title) => `${title} - Quiz Game`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.{js,jsx}', { eager: true });
        // Convert name to path (e.g., 'Admin/Auth/Login' -> './Pages/Admin/Auth/Login.jsx')
        const path = `./Pages/${name}.jsx`;
        const page = pages[path];
        if (!page) {
            throw new Error(`Page "${name}" not found at "${path}"`);
        }
        return page;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(
            <React.StrictMode>
                <App {...props} />
            </React.StrictMode>
        );
    },
    version: '1.0.0',
}); 