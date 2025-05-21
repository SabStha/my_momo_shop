// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/js/app.js',
            'resources/sass/app.scss',
        ]),
    ],
    server: {
        host: 'localhost',
        port: 5173,
    },
});
