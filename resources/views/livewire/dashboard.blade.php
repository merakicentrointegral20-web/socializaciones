<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-9">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5 relative overflow-hidden group hover:border-brand-300 transition">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-700 to-brand-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-extrabold text-brand-950">{{ $resumenModulos->count() }}</p>
                            <p class="text-xs font-medium text-gray-500 mt-1">Módulos activos</p>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5 relative overflow-hidden group hover:border-accent-300 transition">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-accent-500 to-accent-400"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-extrabold text-gray-900">{{ $totalNomina }}</p>
                            <p class="text-xs font-medium text-gray-500 mt-1">Personas en nómina</p>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-accent-50 text-accent-600 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-5 relative overflow-hidden group hover:border-amber-300 transition">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-amber-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-extrabold text-amber-600">{{ $personasConPendientes->count() }}</p>
                            <p class="text-xs font-medium text-gray-500 mt-1">Con módulos pendientes</p>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Resumen por módulo ─────────────────────────────────────────── --}}
            <div class="space-y-3">
                <h3 class="flex items-center gap-2 text-xs font-bold text-brand-900 uppercase tracking-wider px-1">
                    <span class="h-2 w-2 rounded-full bg-accent-500"></span>
                    Tus módulos
                </h3>

                @if ($resumenModulos->isEmpty())
                    <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-10 text-center text-sm text-gray-400">
                        Todavía no tienes módulos activos.
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($resumenModulos as $fila)
                            <a href="{{ route('modulos.seguimiento', $fila->modulo) }}" wire:navigate class="group bg-white rounded-2xl border border-purple-100 shadow-sm hover:shadow-md hover:border-accent-300 transition p-5 flex items-center gap-4">
                                <x-category-icon :categoria="$fila->modulo->categoria" class="h-11 w-11" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-brand-800">{{ $fila->modulo->titulo }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $fila->completados }}/{{ $totalNomina }} completaron · {{ $fila->porcentaje }}%
                                    </p>
                                    <div class="mt-2.5 h-2 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-brand-700 via-brand-500 to-accent-500" style="width: {{ $fila->porcentaje }}%"></div>
                                    </div>
                                </div>
                                @if ($fila->pendientes > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200/60 px-2.5 py-1 text-xs font-semibold text-amber-700 shrink-0">
                                        {{ $fila->pendientes }} {{ Str::plural('pendiente', $fila->pendientes) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-green-50 border border-green-200/60 px-2.5 py-1 text-xs font-semibold text-green-700 shrink-0">
                                        100% al día
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ─── Quién tiene pendientes, para notificar ─────────────────────── --}}
            <div class="space-y-3">
                <h3 class="flex items-center gap-2 text-xs font-bold text-brand-900 uppercase tracking-wider px-1">
                    <span class="h-2 w-2 rounded-full bg-brand-700"></span>
                    Personas con módulos pendientes
                </h3>

                <div class="bg-white rounded-2xl border border-purple-100 shadow-sm divide-y divide-gray-50">
                    @forelse ($personasConPendientes as $fila)
                        <div class="p-5 flex flex-wrap items-center gap-4 hover:bg-brand-50/20 transition">
                            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-gradient-to-tr from-brand-700 via-brand-600 to-accent-500 text-white font-bold text-sm shrink-0 shadow-xs">
                                {{ strtoupper(substr($fila->user->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $fila->user->name }}</p>
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    @foreach ($fila->pendientes as $modulo)
                                        <span class="inline-flex items-center rounded-full bg-amber-50 border border-amber-200/60 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                                            {{ $modulo->titulo }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            @php
                                $primerNombre = explode(' ', trim($fila->user->name))[0];
                                $listaModulos = $fila->pendientes->pluck('titulo')->implode(', ');
                                $empresa = config('xr_rrhh.empresa_nombre', 'MERAKI');
                                $mensaje = "Estimado/a {$primerNombre}, reciba un cordial saludo de parte del Departamento de Talento Humano de {$empresa}.\n\n"
                                    ."Le escribimos para recordarle que tiene pendiente revisar y completar la(s) siguiente(s) capacitación(es): {$listaModulos}.\n\n"
                                    ."Su participación es muy importante para su desarrollo profesional y para el cumplimiento de nuestras normativas internas, por lo que le invitamos a completarla(s) a la brevedad posible.\n\n"
                                    ."Quedamos atentos ante cualquier consulta.\n\n"
                                    ."Saludos cordiales,\nTalento Humano - {$empresa}";
                                $telefonoWhatsapp = $fila->user->telefonoWhatsapp();
                            @endphp
                            <div class="flex items-center gap-3 shrink-0">
                                @if ($telefonoWhatsapp)
                                    <a
                                        href="https://wa.me/{{ $telefonoWhatsapp }}?text={{ rawurlencode($mensaje) }}"
                                        target="_blank"
                                        class="text-xs font-medium text-green-600 hover:text-green-800"
                                    >
                                        WhatsApp →
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300">Sin celular</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            Nadie tiene módulos pendientes. 🎉
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
