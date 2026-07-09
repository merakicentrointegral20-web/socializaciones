<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-9">

            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold text-gray-900">{{ $resumenModulos->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Módulos activos</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalNomina }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Personas en nómina</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold text-amber-600">{{ $personasConPendientes->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Con módulos pendientes</p>
                </div>
            </div>

            {{-- ─── Resumen por módulo ─────────────────────────────────────────── --}}
            <div class="space-y-3">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide px-1">Tus módulos</h3>

                @if ($resumenModulos->isEmpty())
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center text-sm text-gray-400">
                        Todavía no tienes módulos activos.
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($resumenModulos as $fila)
                            <a href="{{ route('modulos.seguimiento', $fila->modulo) }}" wire:navigate class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-brand-200 transition p-5 flex items-center gap-4">
                                <x-category-icon :categoria="$fila->modulo->categoria" class="h-10 w-10" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 truncate group-hover:text-brand-700">{{ $fila->modulo->titulo }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $fila->completados }}/{{ $totalNomina }} completaron · {{ $fila->porcentaje }}%
                                    </p>
                                    <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-brand-500" style="width: {{ $fila->porcentaje }}%"></div>
                                    </div>
                                </div>
                                @if ($fila->pendientes > 0)
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 shrink-0">
                                        {{ $fila->pendientes }} {{ Str::plural('pendiente', $fila->pendientes) }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ─── Quién tiene pendientes, para notificar ─────────────────────── --}}
            <div class="space-y-3">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide px-1">Personas con módulos pendientes</h3>

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">
                    @forelse ($personasConPendientes as $fila)
                        <div class="p-5 flex flex-wrap items-center gap-4">
                            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-gray-100 text-gray-500 font-semibold text-sm shrink-0">
                                {{ strtoupper(substr($fila->user->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ $fila->user->name }}</p>
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    @foreach ($fila->pendientes as $modulo)
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                                            {{ $modulo->titulo }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            @php
                                $primerNombre = explode(' ', trim($fila->user->name))[0];
                                $listaModulos = $fila->pendientes->pluck('titulo')->implode(', ');
                                $mensaje = "Estimado/a {$primerNombre}, reciba un cordial saludo de parte del Departamento de Recursos Humanos de XURI S.A.\n\n"
                                    ."Le escribimos para recordarle que tiene pendiente revisar y completar la(s) siguiente(s) capacitación(es): {$listaModulos}.\n\n"
                                    ."Su participación es muy importante para su desarrollo profesional y para el cumplimiento de nuestras normativas internas, por lo que le invitamos a completarla(s) a la brevedad posible.\n\n"
                                    ."Quedamos atentos ante cualquier consulta.\n\n"
                                    ."Saludos cordiales,\nRecursos Humanos - XURI S.A.";
                                $telefonoWhatsapp = $fila->user->contacto?->telefonoWhatsapp();
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
                                @endif
                                <a
                                    href="mailto:{{ $fila->user->email }}?subject={{ rawurlencode('Recordatorio de capacitaciones pendientes - RRHH XURI S.A.') }}&body={{ rawurlencode($mensaje) }}"
                                    class="text-xs font-medium text-brand-600 hover:text-brand-800"
                                >
                                    Correo →
                                </a>
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
