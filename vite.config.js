import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/form.js',
                'resources/js/review.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@css': path.resolve(__dirname, 'resources/css'),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `@import "resources/css/variables.scss";`
            },
        },
    },
    build: {
        minify: 'esbuild', // Use esbuild for faster builds
        sourcemap: true,   // Enable sourcemaps for debugging
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', 'axios'], // Example of splitting vendor code
                },
            },
        },
    },
});
