// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'; // ✅ Add this line

export default defineConfig({
    plugins: [
        vue(), // ✅ Enable Vue plugin
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
