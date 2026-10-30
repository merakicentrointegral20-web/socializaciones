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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Guía simple de dónde va el colaborador en el proceso. --}}
            @php
                $pasos = [
                    ['label' => 'Leer', 'hecho' => $yaLeido],
                ];

                if ($videos->isNotEmpty()) {
                    $pasos[] = ['label' => 'Videos', 'hecho' => $todosLosVideosCompletados];
                }

                $pasos[] = ['label' => 'Evaluación', 'hecho' => (bool) $certificado || ($ultimoIntento?->aprobado ?? false)];
                $pasos[] = ['label' => 'Certificado', 'hecho' => (bool) $certificado];
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-3 sm:px-6 py-5">
                <div class="flex items-start">
                    @foreach ($pasos as $i => $paso)
                        <div class="flex items-start {{ $loop->last ? '' : 'flex-1' }}">
                            <div class="flex flex-col items-center gap-1.5 w-12 sm:w-auto shrink-0">
                                <div class="h-7 w-7 rounded-full flex items-center justify-center text-xs font-medium {{ $paso['hecho'] ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $paso['hecho'] ? '✓' : $i + 1 }}
                                </div>
                                <span class="text-[10px] sm:text-[11px] leading-tight text-center text-gray-500">{{ $paso['label'] }}</span>
                            </div>
                            @if (! $loop->last)
                                <div class="flex-1 h-px mx-1 sm:mx-2 mt-3.5 {{ $paso['hecho'] ? 'bg-brand-300' : 'bg-gray-200' }}"></div>
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
                <div class="bg-amber-50/60 rounded-2xl border-2 border-amber-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-amber-200 flex items-center gap-3">
                        <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-amber-400 text-white text-base shrink-0">
                            ⚠️
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-amber-900 flex items-center gap-2 flex-wrap">
                                Revisión obligatoria
                                <span class="inline-flex items-center rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
                                    Requerido
                                </span>
                            </h3>
                            <p class="text-xs text-amber-700 mt-0.5">
                                @if ($documentoAbierto)
                                    Ya abriste el material. Puedes marcar el módulo como leído.
                                @else
                                    Debes abrir el material antes de poder marcar este módulo como leído.
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="divide-y divide-amber-100">
                        @foreach ($documentos as $archivo)
                            <a
                                href="{{ route('modulos.archivos.download', [$modulo, $archivo]) }}"
                                target="_blank"
                                @if (! $documentoAbierto) wire:click="abrirDocumento" @endif
                                class="p-4 flex items-center gap-4 hover:bg-amber-100/50 transition"
                            >
                                <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-white ring-1 ring-amber-200 text-amber-700 text-xs font-semibold uppercase shrink-0">
                                    {{ $archivo->tipo }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-800 truncate">{{ $archivo->nombre_original }}</p>
                                    <p class="text-xs text-gray-400">{{ $archivo->tamanoLegible() }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1 text-amber-700 text-xs font-semibold shrink-0">
                                    Abrir →
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex sm:justify-end">
                @if ($yaLeido)
                    <span class="inline-flex items-center gap-2 text-sm text-green-700">
                        ✓ Módulo Leído
                    </span>
                @else
                    <x-primary-button
                        wire:click="marcarLeido"
                        wire:loading.attr="disabled"
                        :disabled="$requiereAbrirDocumento && ! $documentoAbierto"
                        class="w-full sm:w-auto disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed disabled:hover:bg-gray-200"
                    >
                        Marcar como leído
                    </x-primary-button>
                @endif
            </div>

            @if ($yaLeido && $videos->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($videos as $item)
                        @php $video = $item->video; @endphp
                        <div wire:key="video-{{ $video->id }}" class="bg-white rounded-2xl border {{ $item->desbloqueado ? 'border-gray-100' : 'border-gray-100 opacity-60' }} shadow-sm overflow-hidden">
                            <div class="p-5 flex items-start gap-3">
                                <span class="inline-flex items-center justify-center h-7 w-7 rounded-full text-xs font-medium shrink-0 mt-0.5 {{ $item->completado ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $item->completado ? '✓' : $loop->iteration }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-800">{{ $video->titulo }}</p>
                                    @if ($video->descripcion)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $video->descripcion }}</p>
                                    @endif
                                </div>
                            </div>

                            @if ($item->desbloqueado)
                                <div class="px-5 pb-5 space-y-3">
                                    {{-- wire:ignore: el morph de Livewire en cada re-render puede
                                    destruir/recrear el <iframe> si se deja mezclado con contenido
                                    que sí cambia (ej. el botón que pasa a check) -- se aísla para
                                    que el navegador nunca lo toque de nuevo una vez montado. --}}
                                    <div wire:ignore class="aspect-video w-full rounded-xl overflow-hidden bg-gray-900 shadow-sm border border-gray-100">
                                        @if (preg_match('/\.(mp4|webm|ogg)($|\?)/i', $video->embed_url))
                                            <video controls class="w-full h-full" preload="metadata">
                                                <source src="{{ $video->embed_url }}">
                                                Tu navegador no soporta el reproductor de video HTML5.
                                            </video>
                                        @else
                                            <iframe
                                                src="{{ $video->embed_url }}"
                                                class="w-full h-full"
                                                allowfullscreen
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            ></iframe>
                                        @endif
                                    </div>

                                    @if ($item->completado)
                                        <span class="inline-flex items-center gap-2 text-sm text-green-700">
                                            ✓ Video completado
                                        </span>
                                    @else
                                        <x-primary-button wire:click="marcarVideoCompletado({{ $video->id }})" wire:loading.attr="disabled" class="w-full sm:w-auto">
                                            Confirmo que vi este video completo
                                        </x-primary-button>
                                    @endif
                                </div>
                            @else
                                <div class="px-5 pb-5">
                                    <p class="text-xs text-gray-400">🔒 Se desbloquea al completar el video anterior.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

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
            @elseif ($yaLeido && $todosLosVideosCompletados && $intentosAgotados)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-6 space-y-3">
                    <div>
                        <p class="text-sm font-medium text-red-900">Alcanzaste el máximo de intentos</p>
                        <p class="text-xs text-red-700 mt-0.5">
                            Para volver a intentar la evaluación, primero reinicia el módulo: vuelve a leer el contenido y ver los videos.
                        </p>
                    </div>
                    <x-danger-button wire:click="reiniciarModulo" wire:confirm="¿Reiniciar este módulo? Perderás tu progreso de lectura y videos, pero tu historial de intentos se conserva." class="w-full sm:w-auto justify-center">
                        Reiniciar módulo
                    </x-danger-button>
                </div>
            @elseif ($yaLeido && $todosLosVideosCompletados)
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-800">Evaluación</p>
                        @if ($ultimoIntento)
                            <p class="text-xs text-red-600 mt-0.5">
                                No aprobaste tu último intento ({{ $ultimoIntento->score }}/{{ $ultimoIntento->total_preguntas }}).
                                Te {{ $intentosRestantes === 1 ? 'queda 1 intento' : "quedan {$intentosRestantes} intentos" }}.
                            </p>
                        @else
                            <p class="text-xs text-gray-500 mt-0.5">Ya puedes rendir tu evaluación.</p>
                        @endif
                    </div>
                    <a href="{{ route('mis-modulos.evaluacion', $modulo) }}" wire:navigate class="block sm:inline-block">
                        <x-primary-button class="w-full sm:w-auto">Rendir evaluación</x-primary-button>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
