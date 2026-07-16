<div>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mis-modulos.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600">←</a>
            <div>
                <p class="text-xs font-medium text-brand-600 uppercase tracking-wide">{{ $modulo->categoria }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $modulo->titulo }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Guía simple de dónde va el colaborador en el proceso. --}}
            @php
                $pasos = [
                    ['label' => 'Leer', 'hecho' => $yaLeido],
                    ['label' => 'Inducción', 'hecho' => $asistioInduccion],
                    ['label' => 'Evaluación', 'hecho' => (bool) $certificado || ($ultimoIntento?->aprobado ?? false)],
                    ['label' => 'Certificado', 'hecho' => (bool) $certificado],
                ];
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <div class="flex items-center">
                    @foreach ($pasos as $i => $paso)
                        <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                            <div class="flex flex-col items-center gap-1.5 shrink-0">
                                <div class="h-7 w-7 rounded-full flex items-center justify-center text-xs font-medium {{ $paso['hecho'] ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $paso['hecho'] ? '✓' : $i + 1 }}
                                </div>
                                <span class="text-[11px] text-gray-500 whitespace-nowrap">{{ $paso['label'] }}</span>
                            </div>
                            @if (! $loop->last)
                                <div class="flex-1 h-px mx-2 {{ $paso['hecho'] ? 'bg-brand-300' : 'bg-gray-200' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                @if ($modulo->contenido)
                    {{-- Contenido HTML generado por el editor del instructor (rol de
                    confianza, mismo nivel de acceso que ya tiene sobre estos datos). --}}
                    <div class="text-sm text-gray-700 leading-relaxed [&_a]:text-brand-600 [&_a]:underline [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-3 [&_p:last-child]:mb-0">
                        {!! $modulo->contenido !!}
                    </div>
                @else
                    <p class="text-sm text-gray-400">Este módulo todavía no tiene contenido escrito.</p>
                @endif

                @if ($imagenes->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($imagenes as $imagen)
                            <img
                                src="{{ route('modulos.archivos.download', [$modulo, $imagen]) }}"
                                alt="{{ $imagen->nombre_original }}"
                                class="w-full rounded-xl border border-gray-100"
                            >
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($documentos->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="text-sm font-medium text-gray-900">Revisión obligatoria</h3>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ($documentos as $archivo)
                            <a href="{{ route('modulos.archivos.download', [$modulo, $archivo]) }}" target="_blank" class="p-4 flex items-center gap-4 hover:bg-gray-50/70 transition">
                                <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-brand-50 text-brand-700 text-xs font-semibold uppercase shrink-0">
                                    {{ $archivo->tipo }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-700 truncate">{{ $archivo->nombre_original }}</p>
                                    <p class="text-xs text-gray-400">{{ $archivo->tamanoLegible() }}</p>
                                </div>
                                <span class="text-brand-600 text-xs font-medium shrink-0">Abrir en pestaña nueva</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($materialApoyo->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="text-sm font-medium text-gray-900">Material de apoyo</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Opcional, no es necesario para avanzar en el módulo.</p>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach ($materialApoyo as $archivo)
                            <a href="{{ route('modulos.archivos.download', [$modulo, $archivo]) }}" target="_blank" class="p-4 flex items-center gap-4 hover:bg-gray-50/70 transition">
                                <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-brand-50 text-brand-700 text-xs font-semibold uppercase shrink-0">
                                    {{ $archivo->tipo }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-700 truncate">{{ $archivo->nombre_original }}</p>
                                    <p class="text-xs text-gray-400">{{ $archivo->tamanoLegible() }}</p>
                                </div>
                                <span class="text-brand-600 text-xs font-medium shrink-0">Abrir en pestaña nueva</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                @if ($yaLeido)
                    <span class="inline-flex items-center gap-2 text-sm text-green-700">
                        ✓ Ya marcaste este módulo como leído
                    </span>
                @else
                    <x-primary-button wire:click="marcarLeido" wire:loading.attr="disabled">
                        Marcar como leído
                    </x-primary-button>
                @endif
            </div>

            @if ($induccion)
                <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5 text-sm text-brand-900">
                    <p class="font-medium">Tu inducción está agendada</p>
                    <p class="mt-1 text-brand-800">
                        {{ $induccion->capacitacion->fecha->format('d/m/Y \a \l\a\s H:i') }}
                        @if ($induccion->capacitacion->modalidad)
                            · {{ $induccion->capacitacion->modalidad }}
                        @endif
                        @if ($induccion->capacitacion->lugar)
                            · {{ $induccion->capacitacion->lugar }}
                        @endif
                    </p>
                </div>
            @endif

            @if ($certificado)
                <div class="rounded-2xl border border-purple-200 bg-purple-50 p-5 text-sm text-purple-900 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="font-medium">🎓 Certificado emitido · No. {{ $certificado->numero }}</p>
                        <p class="mt-1 text-purple-700 text-xs">
                            {{ $certificado->emitido_at?->format('d/m/Y') }} · código {{ $certificado->codigo_verificacion }}
                        </p>
                    </div>
                    @if ($certificado->pdf_path)
                        <a href="{{ route('certificados.pdf', $certificado) }}" target="_blank">
                            <x-primary-button>Descargar PDF</x-primary-button>
                        </a>
                    @endif
                </div>
            @elseif ($asistioInduccion)
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Evaluación</p>
                        @if ($ultimoIntento)
                            <p class="text-xs text-red-600 mt-0.5">
                                No aprobaste tu último intento ({{ $ultimoIntento->score }}/{{ $ultimoIntento->total_preguntas }}). Puedes volver a intentarlo.
                            </p>
                        @else
                            <p class="text-xs text-gray-500 mt-0.5">Ya puedes rendir tu evaluación.</p>
                        @endif
                    </div>
                    <a href="{{ route('mis-modulos.evaluacion', $modulo) }}" wire:navigate>
                        <x-primary-button>Rendir evaluación</x-primary-button>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
