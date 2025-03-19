import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: '192.168.0.60',
            protocol: 'http'
        },
        watch: {
            usePolling: true
        },
        cors: true
    },
    plugins: [
        laravel({
            input: [
                // Styles
                'resources/css/app.css',
                
                // Core JS
                'resources/js/app.js',
                'resources/js/mobile-menu.js',
                
                // Map Related
                'resources/js/maps/base-map.js',
                'resources/js/maps/claim-map.js',
                'resources/js/maps/profile-map.js',
                'resources/js/maps/review-map.js',
                
                // Claim Related
                'resources/js/claim-form.js',
                'resources/js/claim-document.js',
                'resources/js/claim-review.js',
                'resources/js/claim-resubmit.js',
                'resources/js/claim-accommodation.js',
                'resources/js/claim-export.js',
                'resources/js/accommodation-manager.js',
                'resources/js/accommodation-resubmit.js',
                'resources/js/approval-form.js',
                'resources/js/rejection-form.js',
                
                // Utils
                'resources/js/utils/error-handler.js',
                'resources/js/utils/validation.js',
                'resources/js/utils/logger.js',
                'resources/js/utils/location-manager.js',
                'resources/js/utils/route-calculator.js',
                'resources/js/utils/swal-utils.js',
                'resources/js/utils/map-utils.js',
                'resources/js/utils/marker-view.js',
                'resources/js/utils/rate-limiter.js',
                'resources/js/utils/constants.js',
                
                // Features
                'resources/js/filter.js',
                'resources/js/card-filter.js',
                'resources/js/profile.js',
                'resources/js/register-handler.js',
                'resources/js/users.js',
                'resources/js/admin.js',
                
                // Config
                'resources/js/config.js',
                'resources/js/system-config.js',
                'resources/js/bulk-email.js'
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
