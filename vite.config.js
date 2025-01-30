import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        cors: true
    },
    plugins: [
        laravel({
            input: [
                // Styles
                'resources/css/app.css',
                
                // Core JS
                'resources/js/mobile-menu.js',
                
                // Map Related
                'resources/js/maps/base-map.js',
                'resources/js/maps/claim-map.js',
                'resources/js/maps/profile-map.js',
                'resources/js/maps/review-map.js',
                'resources/js/maps/resubmit-map.js',
                
                // Claim Related
                'resources/js/claim-form.js',
                'resources/js/claim-document.js',
                'resources/js/claim-review.js',
                'resources/js/claim-resubmit.js',
                'resources/js/claim-accommodation.js',
                
                // Utils
                'resources/js/utils/error-handler.js',
                'resources/js/utils/validation.js',
                'resources/js/utils/logger.js',
                'resources/js/utils/location-manager.js',
                'resources/js/utils/route-calculator.js',
                'resources/js/utils/swal-utils.js',
                
                // Features
                'resources/js/filter.js',
                'resources/js/card-filter.js',
                'resources/js/profile.js',
                'resources/js/register-handler.js',
                'resources/js/users.js',
                'resources/js/admin.js',
                
                // Config
                'resources/js/config.js'
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
