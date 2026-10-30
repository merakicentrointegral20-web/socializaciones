<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', config('xr_rrhh.empresa_nombre', 'MERAKI') . ' Capacitaciones') }}</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex bg-white">
            <!-- Panel de marca institucional MERAKI -->
            <div class="hidden lg:flex lg:w-1/2 xl:w-5/12 bg-gradient-to-br from-[#3b0944] via-[#681878] to-[#e6007e] flex-col justify-between p-12 text-white relative overflow-hidden">
                <!-- Efectos decorativos de fondo -->
                <div class="absolute -right-20 -top-20 h-96 w-96 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-[#e6007e]/20 blur-3xl"></div>

                <div class="relative z-10">
                    <div class="inline-flex p-3 bg-white rounded-2xl shadow-xl shadow-purple-950/30">
                        <x-application-logo class="h-16 w-auto object-contain" />
                    </div>
                </div>

                <div class="relative z-10 space-y-4 max-w-md">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-xs font-semibold tracking-wide backdrop-blur-sm text-pink-100 border border-white/20">
                        <span class="h-2 w-2 rounded-full bg-[#e6007e] animate-pulse"></span>
                        Portal de Formación Continua
                    </div>
                    <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
                        Capacitación, Inducción y Certificación
                    </h1>
                    <p class="text-purple-100 text-base leading-relaxed">
                        Bienvenido a la plataforma de aprendizaje de <strong class="text-white font-semibold">{{ config('xr_rrhh.empresa_nombre', 'MERAKI') }}</strong>. 
                        Revisa tus módulos asignados, completa los videos obligatorios y obtén tus certificados oficiales.
                    </p>
                </div>

                <div class="relative z-10 text-sm text-purple-200/80 flex items-center justify-between">
                    <span>&copy; {{ date('Y') }} {{ config('xr_rrhh.empresa_nombre', 'MERAKI') }}</span>
                    <span class="text-xs text-pink-200">Centro de Atención Integral</span>
                </div>
            </div>

            <!-- Panel del formulario de inicio de sesión -->
            <div class="flex flex-1 flex-col justify-center items-center px-6 py-12 sm:px-10 lg:px-16">
                <div class="w-full max-w-sm">
                    <div class="flex justify-center mb-8 lg:hidden">
                        <div class="p-3 bg-white rounded-2xl shadow-md border border-purple-100">
                            <x-application-logo class="h-16 w-auto object-contain" />
                        </div>
                    </div>

                    <div class="bg-white">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
