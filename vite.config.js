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
                'resources/js/claims-view-toggle.js',
                'resources/js/filter.js',
                'resources/js/card-filter.js',
                'resources/css/app.css',
                /*
                'resources/css/others/animations.css',
                'resources/css/layouts/navigation.css',
                'resources/css/components/buttons.css',
                'resources/css/components/breadcrumbs.css',
                'resources/css/components/status-action.css',
                'resources/css/components/icons.css',
                'resources/css/components/typography.css',
                'resources/css/components/table.css',
                */
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
