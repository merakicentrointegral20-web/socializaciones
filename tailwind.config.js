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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Basado en el azul institucional de Xuri (#1a4c9a)
                brand: {
                    50: '#eef4fc',
                    100: '#d9e6f7',
                    200: '#b3cdef',
                    300: '#82ade3',
                    400: '#4d86d2',
                    500: '#2c65b8',
                    600: '#1a4c9a',
                    700: '#163d7c',
                    800: '#143363',
                    900: '#132c52',
                    950: '#0c1b34',
                },
            },
        },
    },

    plugins: [forms],
};
