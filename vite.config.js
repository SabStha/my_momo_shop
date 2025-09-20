import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '0.0.0.0',
    },
    build: {
        outDir: 'public/build',
        assetsDir: 'assets',
        manifest: true,
        target: 'es2020',
        cssCodeSplit: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs', 'axios', 'jquery'],
                },
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/clock-system.js',
                'resources/js/cart.js',
                'resources/js/wallet.js'
            ],
            refresh: true,
            buildDirectory: 'build',
        }),
    ],
});
