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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                manrope: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#00236f',
                'primary-container': '#1e3a8a',
                'on-primary': '#ffffff',
                'on-primary-container': '#90a8ff',
                'primary-fixed-dim': '#b6c4ff',
                surface: '#f7f9fb',
                'surface-bright': '#f7f9fb',
                'surface-dim': '#d8dadc',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#f2f4f6',
                'surface-container': '#eceef0',
                'surface-container-high': '#e6e8ea',
                'surface-container-highest': '#e0e3e5',
                'on-surface': '#191c1e',
                'on-surface-variant': '#444651',
                'surface-variant': '#e0e3e5',
                'outline': '#757682',
                'outline-variant': '#c5c5d3',
                secondary: '#515f74',
                'secondary-container': '#d5e3fc',
                'on-secondary': '#ffffff',
                'on-secondary-container': '#57657a',
                error: '#ba1a1a',
                'error-container': '#ffdad6',
                'on-error': '#ffffff',
                'on-error-container': '#93000a',
            },
            borderRadius: {
                DEFAULT: '0.125rem',
                lg: '0.25rem',
                xl: '0.5rem',
                '2xl': '1rem',
                full: '9999px',
            },
            boxShadow: {
                editorial: '0 24px 40px -4px rgba(25, 28, 30, 0.08)',
            },
        },
    },

    plugins: [forms],
};
