import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    optimizeDeps: {
        include: ['react', 'react-dom'],
    },
    css: {
        postcss: './postcss.config.js',
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            clientPort: 5173,
            host: 'localhost',
            protocol: 'ws'
        },
        watch: {
            usePolling: true,
            interval: 500,
            ignored: [
                '**/node_modules/**',
                '**/vendor/**',
                '**/storage/**',
                '**/public/**',
                '**/bootstrap/cache/**'
            ],
        },
    },
});
