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
            },
            colors: {
                // Systemic Logic — Google Stitch design system
                navy: {
                    DEFAULT: '#1e293b',
                    50: '#f8f9ff',
                    100: '#eef2ff',
                    200: '#d8e3fb',
                    300: '#bcc7de',
                    400: '#8590a6',
                    500: '#545f73',
                    600: '#3c475a',
                    700: '#213145',
                    800: '#111c2d',
                    900: '#0b1c30',
                },
                royal: {
                    DEFAULT: '#2563eb',
                    50: '#eff6ff',
                    100: '#dbe1ff',
                    200: '#b4c5ff',
                    300: '#7ba4ff',
                    400: '#316bf3',
                    500: '#2563eb',
                    600: '#0051d5',
                    700: '#003ea8',
                    800: '#0b1c30',
                },
                surface: {
                    DEFAULT: '#f8f9ff',
                    card: '#ffffff',
                    muted: '#f1f5f9',
                    border: '#e2e8f0',
                    outline: '#cbd5e1',
                    dim: '#cbdbf5',
                },
                success: {
                    DEFAULT: '#16a34a',
                    soft: '#ecfdf5',
                },
                warning: {
                    DEFAULT: '#b45309',
                    soft: '#fef3c7',
                },
                danger: {
                    DEFAULT: '#dc2626',
                    soft: '#fee2e2',
                },
            },
            borderRadius: {
                sm: '0.25rem',
                DEFAULT: '0.5rem',
                md: '0.75rem',
                lg: '1rem',
                xl: '1.5rem',
            },
            boxShadow: {
                card: 'none',
                dropdown: '0 10px 15px -3px rgba(0, 0, 0, 0.05)',
            },
        },
    },

    plugins: [forms],
};
