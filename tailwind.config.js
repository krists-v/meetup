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
            colors: {
                pitch: {
                    50: 'rgb(var(--pitch-50) / <alpha-value>)',
                    100: 'rgb(var(--pitch-100) / <alpha-value>)',
                    200: 'rgb(var(--pitch-200) / <alpha-value>)',
                    300: 'rgb(var(--pitch-300) / <alpha-value>)',
                    400: 'rgb(var(--pitch-400) / <alpha-value>)',
                    500: 'rgb(var(--pitch-500) / <alpha-value>)',
                    600: 'rgb(var(--pitch-600) / <alpha-value>)',
                    700: 'rgb(var(--pitch-700) / <alpha-value>)',
                    800: 'rgb(var(--pitch-800) / <alpha-value>)',
                    900: 'rgb(var(--pitch-900) / <alpha-value>)',
                    950: 'rgb(var(--pitch-950) / <alpha-value>)',
                },
                ink: {
                    DEFAULT: 'rgb(var(--ink) / <alpha-value>)',
                    muted: 'rgb(var(--ink-muted) / <alpha-value>)',
                    faint: 'rgb(var(--ink-faint) / <alpha-value>)',
                },
                canvas: 'rgb(var(--canvas) / <alpha-value>)',
                line: 'rgb(var(--line) / <alpha-value>)',
                sand: 'rgb(var(--sand) / <alpha-value>)',
                success: {
                    DEFAULT: 'rgb(var(--success) / <alpha-value>)',
                    soft: 'rgb(var(--success-soft) / <alpha-value>)',
                },
                warn: {
                    DEFAULT: 'rgb(var(--warn) / <alpha-value>)',
                    soft: 'rgb(var(--warn-soft) / <alpha-value>)',
                },
                danger: {
                    DEFAULT: 'rgb(var(--danger) / <alpha-value>)',
                    soft: 'rgb(var(--danger-soft) / <alpha-value>)',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                xs: ['0.75rem', { lineHeight: '1rem' }],
                sm: ['0.875rem', { lineHeight: '1.25rem' }],
                base: ['1rem', { lineHeight: '1.5rem' }],
                lg: ['1.125rem', { lineHeight: '1.75rem' }],
                xl: ['1.25rem', { lineHeight: '1.75rem' }],
                '2xl': ['1.5rem', { lineHeight: '2rem' }],
                '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
                '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
            },
            spacing: {
                4.5: '1.125rem',
                18: '4.5rem',
            },
            borderRadius: {
                control: 'var(--radius-control)',
                card: 'var(--radius-card)',
            },
            boxShadow: {
                card: 'var(--shadow-card)',
                lift: 'var(--shadow-lift)',
            },
            maxWidth: {
                content: '40rem',
            },
        },
    },

    plugins: [forms],
};
