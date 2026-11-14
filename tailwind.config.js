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
                    50: 'var(--color-pitch-50)',
                    100: 'var(--color-pitch-100)',
                    200: 'var(--color-pitch-200)',
                    300: 'var(--color-pitch-300)',
                    400: 'var(--color-pitch-400)',
                    500: 'var(--color-pitch-500)',
                    600: 'var(--color-pitch-600)',
                    700: 'var(--color-pitch-700)',
                    800: 'var(--color-pitch-800)',
                    900: 'var(--color-pitch-900)',
                    950: 'var(--color-pitch-950)',
                },
                ink: {
                    DEFAULT: 'var(--color-ink)',
                    muted: 'var(--color-ink-muted)',
                    faint: 'var(--color-ink-faint)',
                },
                canvas: 'var(--color-canvas)',
                line: 'var(--color-line)',
                sand: 'var(--color-sand)',
                success: {
                    DEFAULT: 'var(--color-success)',
                    soft: 'var(--color-success-soft)',
                },
                warn: {
                    DEFAULT: 'var(--color-warn)',
                    soft: 'var(--color-warn-soft)',
                },
                danger: {
                    DEFAULT: 'var(--color-danger)',
                    soft: 'var(--color-danger-soft)',
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
