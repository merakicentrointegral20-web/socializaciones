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
                // Paleta inspirada en el logo de MERAKI (Morado, Fucsia y Blanco institucional)
                brand: {
                    50: '#fcf6fe',
                    100: '#f7ebfd',
                    200: '#edd5fa',
                    300: '#dfb4f5',
                    400: '#cb86ee',
                    500: '#a847d8',
                    600: '#851c99',
                    700: '#681878', // Morado exacto del logo MERAKI
                    800: '#52125e',
                    900: '#3b0944',
                    950: '#26042c',
                },
                meraki: {
                    purple: '#681878',
                    fuchsia: '#e6007e', // Fucsia exacto de los lentes y detalles del logo MERAKI
                    pink: '#f02293',
                    dark: '#26042c',
                    light: '#fdf2f8',
                },
                accent: {
                    50: '#fef2f8',
                    100: '#fde5f3',
                    200: '#fbcde8',
                    300: '#f7a4d5',
                    400: '#f06bb9',
                    500: '#e6007e', // Fucsia exacto del logo MERAKI
                    600: '#d0006e',
                    700: '#ae005c',
                    800: '#8e034c',
                    900: '#750841',
                    950: '#460023',
                },
            },
        },
    },

    plugins: [forms],
};
