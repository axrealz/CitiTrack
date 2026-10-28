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
                brand: {
                    bg:     '#0F172A',
                    accent: '#F59E0B',
                },
                role: {
                    admin:   '#4F46E5',
                    cashier: '#059669',
                    stocker: '#0284C7',
                },
            },
        },
    },

    plugins: [forms],
};