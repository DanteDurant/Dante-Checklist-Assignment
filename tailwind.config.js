import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ui: {
                    canvas: 'rgb(var(--ui-canvas) / <alpha-value>)',
                    surface: 'rgb(var(--ui-surface) / <alpha-value>)',
                    muted: 'rgb(var(--ui-muted) / <alpha-value>)',
                    elevated: 'rgb(var(--ui-elevated) / <alpha-value>)',
                    border: 'rgb(var(--ui-border) / <alpha-value>)',
                    ring: 'rgb(var(--ui-ring) / <alpha-value>)',
                    fg: 'rgb(var(--ui-fg) / <alpha-value>)',
                    'fg-muted': 'rgb(var(--ui-fg-muted) / <alpha-value>)',
                    'fg-subtle': 'rgb(var(--ui-fg-subtle) / <alpha-value>)',
                    fill: 'rgb(var(--ui-fill) / <alpha-value>)',
                    'fill-border': 'rgb(var(--ui-fill-border) / <alpha-value>)',
                    'fill-placeholder': 'rgb(var(--ui-fill-placeholder) / <alpha-value>)',
                    accent: 'rgb(var(--ui-accent) / <alpha-value>)',
                    'accent-fg': 'rgb(var(--ui-accent-fg) / <alpha-value>)',
                    'accent-hover': 'rgb(var(--ui-accent-hover) / <alpha-value>)',
                },
            },
            ringOffsetColor: {
                canvas: 'rgb(var(--ui-canvas) / <alpha-value>)',
                surface: 'rgb(var(--ui-surface) / <alpha-value>)',
            },
        },
    },
    plugins: [],
};
