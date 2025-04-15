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
                'resources/js/app.js',
                'resources/js/components/mobile-menu.js',
                'resources/js/components/filter.js',
                
                // Map Related
                'resources/js/maps/base-map.js',
                'resources/js/maps/claim-map.js',
                'resources/js/maps/profile-map.js',
                'resources/js/maps/review-map.js',
                
                // Claim Related
                'resources/js/claims/claim-form.js',
                'resources/js/claims/claim-document.js',
                'resources/js/claims/claim-review.js',
                'resources/js/claims/claim-resubmit.js',
                'resources/js/claims/claim-accommodation.js',
                'resources/js/claims/claim-export.js',
                'resources/js/claims/accommodation-manager.js',
                'resources/js/claims/accommodation-resubmit.js',
                'resources/js/claims/approval-form.js',
                'resources/js/claims/rejection-form.js',
                'resources/js/claims/bulk-email.js',
                
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
                
                // Features & Components
                'resources/js/components/filter.js',
                'resources/js/components/card-filter.js',
                'resources/js/user/profile.js',
                'resources/js/auth/register-handler.js',
                'resources/js/user/users.js',
                'resources/js/admin/admin.js',
                
                // Config & Admin
                'resources/js/config.js',
                'resources/js/admin/system-config.js',
                
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
