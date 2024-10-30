import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/form.js',
                'resources/js/review.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        target: 'esnext', // This enables support for top-level await
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', 'axios']
                }
            }
        }
    }
});
