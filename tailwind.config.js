import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "wgg-black": {
                   50: "#f6f6f6",
                   100: "#e7e7e7",
                   200: "#d1d1d1",
                   300: "#b0b0b0",
                   400: "#888888",
                   500: "#6d6d6d",
                   600: "#5d5d5d",
                   700: "#4f4f4f",
                   800: "#454545",
                   900: "#3d3d3d",
                   950: "#242424",
                },
                "wgg-gray": "#646464",
                "wgg-white": "#FFFEFE",
                "wgg-border": "rgba(100, 100, 100, 0.25)",
             },
            keyframes: {
                 'popup-show': {
                    '0%': { opacity: 0, transform: 'scale(0.95)' },
                    '100%': { opacity: 1, transform: 'scale(1)' },
                 },
                 'popup-hide': {
                     '0%': { opacity: 1, transform: 'scale(1)' },
                     '100%': { opacity: 0, transform: 'scale(0.95)' },
                 }
             },
             animation: {
                 'popup-show': 'popup-show 0.2s ease-out forwards',
                 'popup-hide': 'popup-hide 0.15s ease-in forwards',
             }
        },
    },

    plugins: [forms],
};
