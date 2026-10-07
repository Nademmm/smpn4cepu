import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                cream: '#F1EBDA',
                navy: '#1B2A4A',
                ink: {
                    soft: '#4B5563',
                    mute: '#5B6577',
                },
                brand: {
                    DEFAULT: '#1F5FBF',
                    dark: '#163D8A',
                    deep: '#0D2960',
                },
                sun: {
                    DEFAULT: '#F5B700',
                    soft: '#FFE3A0',
                },
                sky: {
                    soft: '#EEF3FB',
                },
                grape: '#7C3AED',
            },
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                card: '20px',
            },
        },
    },

    plugins: [forms, typography],
};
