import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Instrument Sans"', '"Segoe UI"', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Helvetica Neue"', 'Arial', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ksu: {
                    100: '#e6f6ec',
                    400: '#5ec489',
                    500: '#38a169',
                    600: '#2a8a57',
                    700: '#1c6d45',
                    800: '#155738',
                    900: '#0f3c26',
                },
                gold: '#c9a227',
                crimson: '#b22020',
            },
            boxShadow: {
                ksu: '0 6px 20px rgba(16, 61, 38, .12)',
            },
            borderRadius: {
                '2xl': '1.25rem',
            },
        },
    },

    plugins: [forms],
};
