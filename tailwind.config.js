import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Models/Event.php',
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
                activity: {
                    padel: 'rgb(var(--activity-padel) / <alpha-value>)',
                    'padel-soft': 'rgb(var(--activity-padel-soft) / <alpha-value>)',
                    basketball: 'rgb(var(--activity-basketball) / <alpha-value>)',
                    'basketball-soft': 'rgb(var(--activity-basketball-soft) / <alpha-value>)',
                    football: 'rgb(var(--activity-football) / <alpha-value>)',
                    'football-soft': 'rgb(var(--activity-football-soft) / <alpha-value>)',
                    tennis: 'rgb(var(--activity-tennis) / <alpha-value>)',
                    'tennis-soft': 'rgb(var(--activity-tennis-soft) / <alpha-value>)',
                    hockey: 'rgb(var(--activity-hockey) / <alpha-value>)',
                    'hockey-soft': 'rgb(var(--activity-hockey-soft) / <alpha-value>)',
                    volleyball: 'rgb(var(--activity-volleyball) / <alpha-value>)',
                    'volleyball-soft': 'rgb(var(--activity-volleyball-soft) / <alpha-value>)',
                    other: 'rgb(var(--activity-other) / <alpha-value>)',
                    'other-soft': 'rgb(var(--activity-other-soft) / <alpha-value>)',
                    'table-tennis': 'rgb(var(--activity-table-tennis) / <alpha-value>)',
                    'table-tennis-soft': 'rgb(var(--activity-table-tennis-soft) / <alpha-value>)',
                    bowling: 'rgb(var(--activity-bowling) / <alpha-value>)',
                    'bowling-soft': 'rgb(var(--activity-bowling-soft) / <alpha-value>)',
                    climbing: 'rgb(var(--activity-climbing) / <alpha-value>)',
                    'climbing-soft': 'rgb(var(--activity-climbing-soft) / <alpha-value>)',
                    running: 'rgb(var(--activity-running) / <alpha-value>)',
                    'running-soft': 'rgb(var(--activity-running-soft) / <alpha-value>)',
                    cycling: 'rgb(var(--activity-cycling) / <alpha-value>)',
                    'cycling-soft': 'rgb(var(--activity-cycling-soft) / <alpha-value>)',
                    hiking: 'rgb(var(--activity-hiking) / <alpha-value>)',
                    'hiking-soft': 'rgb(var(--activity-hiking-soft) / <alpha-value>)',
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
                '5xl': ['3rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
                '6xl': ['3.75rem', { lineHeight: '1.05', letterSpacing: '-0.02em' }],
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
