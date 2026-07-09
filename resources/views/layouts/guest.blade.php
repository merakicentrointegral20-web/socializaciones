<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Xuri RRHH') }}</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-white">
            <!-- Panel de marca (oculto en pantallas pequeñas) -->
            <div class="hidden lg:flex lg:w-1/2 xl:w-2/5 bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 flex-col justify-between p-12 text-white relative overflow-hidden">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/5"></div>
                <div class="absolute -left-16 bottom-0 h-72 w-72 rounded-full bg-white/5"></div>

                <div class="relative">
                    <img src="{{ asset('img/impxurisa-04.svg') }}" alt="Xuri" class="h-14 w-auto">
                </div>

                <div class="relative space-y-4 max-w-md">
                    <h1 class="text-3xl font-semibold leading-tight">
                        Portal de Capacitación
                    </h1>
                    <p class="text-brand-100 text-base leading-relaxed">
                        Un solo lugar para tus capacitaciones, evaluaciones y certificados.
                        Consulta qué módulos te faltan, cuándo es tu próxima sesión y tu progreso,
                        todo con la misma cuenta que ya usas en Xuri.
                    </p>
                </div>

                <div class="relative text-sm text-brand-200">
                    &copy; {{ date('Y') }} Xuri
                </div>
            </div>

            <!-- Panel del formulario -->
            <div class="flex flex-1 flex-col justify-center items-center px-6 py-12 sm:px-10">
                <div class="w-full max-w-sm">
                    <div class="flex justify-center mb-8 lg:hidden">
                        <img src="{{ asset('img/impxurisa-04.svg') }}" alt="Xuri" class="h-14 w-auto">
                    </div>

                    <div class="bg-white">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
