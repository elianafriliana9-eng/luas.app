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
                primary: {
                    DEFAULT: '#1D4ED8', // blue-700
                    light: '#3B82F6', // blue-500
                    dark: '#1E40AF', // blue-800
                },
                secondary: {
                    DEFAULT: '#059669', // emerald-600
                    light: '#10B981', // emerald-500
                    dark: '#047857', // emerald-700
                },
                tertiary: {
                    DEFAULT: '#F59E0B', // amber-500
                    light: '#FBBF24', // amber-400
                    dark: '#D97706', // amber-600
                },
                neutral: {
                    DEFAULT: '#1E293B', // slate-800
                    light: '#64748B', // slate-500
                    dark: '#0F172A', // slate-900
                },
                danger: {
                    DEFAULT: '#DC2626', // red-600
                },
                surface: {
                    DEFAULT: '#F8FAFC', // slate-50
                    container: '#F1F5F9', // slate-100
                    lowest: '#FFFFFF', // white
                },
                on: {
                    surface: '#0F172A',
                    primary: '#FFFFFF',
                    secondary: '#FFFFFF',
                    tertiary: '#FFFFFF',
                    danger: '#FFFFFF'
                }
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                headline: ['Plus Jakarta Sans', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
                label: ['Inter', 'sans-serif'],
                data: ['JetBrains Mono', 'monospace']
            },
            borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" }
        },
    },

    plugins: [forms],
};
