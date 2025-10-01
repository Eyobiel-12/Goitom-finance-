import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        container: {
            center: true,
            padding: {
                DEFAULT: '1rem',
                sm: '1.25rem',
                lg: '2rem',
                xl: '2rem',
                '2xl': '2.5rem',
            },
            screens: {
                sm: '640px',
                md: '768px',
                lg: '1024px',
                xl: '1280px',
                '2xl': '1440px',
            },
        },
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#fff1f0',
                    100: '#ffe1de',
                    200: '#ffc3bd',
                    300: '#ff9b92',
                    400: '#f97366',
                    500: '#dc2626', // primary red
                    600: '#c21f20',
                    700: '#a1191a',
                    800: '#821315',
                    900: '#6b1011',
                },
                ink: {
                    900: '#0f172a',
                    800: '#1f2937',
                    700: '#334155',
                    600: '#475569',
                    500: '#64748b',
                },
            },
            boxShadow: {
                card: '0 14px 34px 0 rgba(0,0,0,0.08)',
                cardStrong: '0 18px 44px 0 rgba(0,0,0,0.12)',
            },
            borderRadius: {
                xl: '14px',
            },
        },
    },

    plugins: [forms],
};
