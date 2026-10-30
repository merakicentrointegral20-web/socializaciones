<div>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('modulos.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600 shrink-0">←</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
                    {{ $modulo->titulo }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('modulos.asignacion', $modulo) }}"
                    wire:navigate
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 shadow-sm hover:bg-brand-50 transition"
                >
                    Asignar
                </a>
                <a
                    href="{{ route('modulos.seguimiento', $modulo) }}"
                    wire:navigate
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 rounded-lg bg-gradient-to-r from-brand-700 via-brand-600 to-accent-500 hover:from-brand-800 hover:to-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:shadow transition"
                >
                    Ver seguimiento →
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ─── Datos del módulo ──────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                @if ($editandoDatos)
                    <div class="space-y-4">
                        <h3 class="font-medium text-gray-900">Datos del módulo</h3>

                        <div>
                            <x-input-label for="titulo" value="Título" />
                            <x-text-input wire:model="titulo" id="titulo" class="w-full" />
                            <x-input-error :messages="$errors->get('titulo')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="categoria" value="Categoría" />
                            <select wire:model="categoria" id="categoria" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Selecciona una categoría</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('categoria')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label value="Tipo de inducción" />
                            <div class="flex gap-4 mt-1">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio" wire:model="tipoInduccion" value="presencial" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                                    Presencial
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="radio" wire:model="tipoInduccion" value="virtual" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                                    Virtual
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('tipoInduccion')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="contenido" value="Contenido" />
                            <x-rich-editor model="contenido" :value="$contenido" class="rounded-lg border border-gray-300 overflow-hidden" />
                            <x-input-error :messages="$errors->get('contenido')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="umbralAprobacion" value="Umbral de aprobación" />
                                <div class="flex items-center gap-2">
                                    <x-text-input wire:model="umbralAprobacion" id="umbralAprobacion" type="number" min="1" max="10" class="w-full" />
                                    <span class="text-sm text-gray-400 shrink-0">/ 10</span>
                                </div>
                                <x-input-error :messages="$errors->get('umbralAprobacion')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="fechaLimite" value="Fecha límite" />
                                <x-text-input wire:model="fechaLimite" id="fechaLimite" type="date" class="w-full" />
                                <x-input-error :messages="$errors->get('fechaLimite')" class="mt-1" />
                            </div>

                            <div class="flex items-end pb-2.5">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" wire:model="activo" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    Activo
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 mt-2 border-t border-gray-100">
                            <x-secondary-button wire:click="cancelarEdicionDatos">
                                Cancelar
                            </x-secondary-button>
                            <x-primary-button wire:click="save" wire:loading.attr="disabled" class="px-6">
                                Guardar
                            </x-primary-button>
                        </div>
                    </div>
                @else
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-medium text-gray-900 truncate">{{ $titulo }}</h3>
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">
                                    {{ $categoria }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    {{ $tipoInduccion === 'virtual' ? 'Virtual' : 'Presencial' }}
                                </span>
                                @if ($activo)
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Inactivo</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-1.5">
                                Umbral de aprobación: {{ $umbralAprobacion }}/10
                                @if ($fechaLimite)
                                    · Fecha límite: {{ \Illuminate\Support\Carbon::parse($fechaLimite)->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        <x-secondary-button wire:click="editarDatos" class="shrink-0">
                            Editar
                        </x-secondary-button>
                    </div>
                @endif
            </div>

            {{-- ─── Revisión obligatoria (diapositivas / PDF) ─────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Revisión obligatoria</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Las imágenes se muestran incrustadas dentro del contenido cuando el colaborador lee el
                        módulo. Los PDF y PPT solo quedan disponibles como descarga, no se muestran incrustados.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($archivosObligatorios as $archivo)
                        <div class="p-5 flex items-center gap-4">
                            <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-brand-50 text-brand-700 text-xs font-semibold uppercase shrink-0">
                                {{ $archivo->tipo }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-800 truncate">{{ $archivo->nombre_original }}</p>
                                <p class="text-xs text-gray-500">{{ $archivo->tamanoLegible() }}</p>
                            </div>
                            <a href="{{ route('modulos.archivos.download', [$modulo, $archivo]) }}" class="text-brand-600 text-sm font-medium hover:text-brand-800" target="_blank">
                                Ver
                            </a>
                            <x-danger-button wire:click="eliminarArchivo({{ $archivo->id }})" wire:confirm="¿Eliminar este archivo?">
                                Eliminar
                            </x-danger-button>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            Todavía no hay materiales de revisión obligatoria en este módulo.
                        </div>
                    @endforelse
                </div>

                <div class="p-6 bg-gray-50 space-y-3">
                    <x-input-label for="nuevosArchivosObligatorios" value="Subir diapositivas, PDF o imágenes" />
                    <input
                        type="file"
                        wire:model="nuevosArchivosObligatorios"
                        id="nuevosArchivosObligatorios"
                        multiple
                        accept=".pdf,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.gif"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100"
                    >
                    <p class="text-xs text-gray-400">PDF, PPT, PPTX, JPG, PNG, WEBP o GIF, máximo 100 MB por archivo.</p>
                    <x-input-error :messages="$errors->get('nuevosArchivosObligatorios.*')" class="mt-1" />

                    <div wire:loading wire:target="nuevosArchivosObligatorios" class="text-xs text-gray-400">
                        Subiendo...
                    </div>

                    @if (! empty($nuevosArchivosObligatorios))
                        <x-primary-button wire:click="subirArchivosObligatorios" wire:loading.attr="disabled">
                            Guardar materiales
                        </x-primary-button>
                    @endif
                </div>
            </div>

            {{-- ─── Material de apoyo (opcional, no bloquea el avance) ────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Material de apoyo</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Material adicional del instructor. El colaborador lo puede consultar, pero no es
                        obligatorio para avanzar en el módulo.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($archivosApoyo as $archivo)
                        <div class="p-5 flex items-center gap-4">
                            <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-brand-50 text-brand-700 text-xs font-semibold uppercase shrink-0">
                                {{ $archivo->tipo }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-800 truncate">{{ $archivo->nombre_original }}</p>
                                <p class="text-xs text-gray-500">{{ $archivo->tamanoLegible() }}</p>
                            </div>
                            <a href="{{ route('modulos.archivos.download', [$modulo, $archivo]) }}" class="text-brand-600 text-sm font-medium hover:text-brand-800" target="_blank">
                                Ver
                            </a>
                            <x-danger-button wire:click="eliminarArchivo({{ $archivo->id }})" wire:confirm="¿Eliminar este archivo?">
                                Eliminar
                            </x-danger-button>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            Todavía no hay material de apoyo en este módulo.
                        </div>
                    @endforelse
                </div>

                <div class="p-6 bg-gray-50 space-y-3">
                    <x-input-label for="nuevosArchivosApoyo" value="Subir diapositivas, PDF o imágenes" />
                    <input
                        type="file"
                        wire:model="nuevosArchivosApoyo"
                        id="nuevosArchivosApoyo"
                        multiple
                        accept=".pdf,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.gif"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100"
                    >
                    <p class="text-xs text-gray-400">PDF, PPT, PPTX, JPG, PNG, WEBP o GIF, máximo 100 MB por archivo.</p>
                    <x-input-error :messages="$errors->get('nuevosArchivosApoyo.*')" class="mt-1" />

                    <div wire:loading wire:target="nuevosArchivosApoyo" class="text-xs text-gray-400">
                        Subiendo...
                    </div>

                    @if (! empty($nuevosArchivosApoyo))
                        <x-primary-button wire:click="subirArchivosApoyo" wire:loading.attr="disabled">
                            Guardar material de apoyo
                        </x-primary-button>
                    @endif
                </div>
            </div>

            {{-- ─── Videos (OneDrive, Google Drive, YouTube o Vimeo) ─────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Videos</h3>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($videos as $i => $video)
                        <div class="p-5 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-brand-50 text-sm font-semibold text-brand-700 shrink-0 mt-0.5">
                                        {{ $i + 1 }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-base text-gray-900 font-semibold">{{ $video->titulo }}</h4>
                                        @if ($video->descripcion)
                                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $video->descripcion }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0 self-end sm:self-auto">
                                    <button type="button" wire:click="moverVideoArriba({{ $video->id }})" @if ($i === 0) disabled @endif class="h-8 w-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-800 disabled:opacity-25 disabled:hover:bg-transparent" title="Mover arriba">
                                        ↑
                                    </button>
                                    <button type="button" wire:click="moverVideoAbajo({{ $video->id }})" @if ($i === $videos->count() - 1) disabled @endif class="h-8 w-8 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-800 disabled:opacity-25 disabled:hover:bg-transparent" title="Mover abajo">
                                        ↓
                                    </button>
                                    <div class="w-px h-5 bg-gray-200 mx-1"></div>
                                    <button type="button" wire:click="eliminarVideo({{ $video->id }})" wire:confirm="¿Eliminar este video? Esta acción no se puede deshacer." class="h-8 w-8 rounded-lg border border-red-200 text-red-600 hover:bg-red-50" title="Eliminar video">
                                        ✕
                                    </button>
                                </div>
                            </div>

                            {{-- Reproductor de video embebido directamente --}}
                            <div wire:ignore class="aspect-video w-full max-w-2xl rounded-xl overflow-hidden bg-gray-900 shadow-sm border border-gray-200">
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

                            <div class="flex items-center justify-between text-xs text-gray-400 pt-1">
                                <span class="truncate max-w-md">Enlace: {{ $video->embed_url }}</span>
                                <a href="{{ $video->embed_url }}" target="_blank" class="text-brand-600 hover:text-brand-800 font-medium inline-flex items-center gap-1 shrink-0 ml-2">
                                    <span>Abrir en nueva pestaña</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            Todavía no hay videos en este módulo.
                        </div>
                    @endforelse
                </div>

                <div class="p-6 bg-gray-50 space-y-3">
                    <div>
                        <x-input-label for="nuevoVideoTitulo" value="Título" />
                        <x-text-input wire:model="nuevoVideoTitulo" id="nuevoVideoTitulo" class="w-full" />
                        <x-input-error :messages="$errors->get('nuevoVideoTitulo')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="nuevoVideoDescripcion" value="Descripción (opcional)" />
                        <textarea wire:model="nuevoVideoDescripcion" id="nuevoVideoDescripcion" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        <x-input-error :messages="$errors->get('nuevoVideoDescripcion')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="nuevoVideoUrl" value="Link del video o código para insertar" />
                        <textarea wire:model="nuevoVideoUrl" id="nuevoVideoUrl" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="OneDrive (código de Insertar), Google Drive, YouTube, Vimeo, video MP4..."></textarea>
                        <p class="text-xs text-gray-400 mt-1">Soporta videos directos MP4, YouTube, Vimeo, Google Drive o OneDrive (en OneDrive usa la opción "Insertar" y copia el código).</p>
                        <x-input-error :messages="$errors->get('nuevoVideoUrl')" class="mt-1" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button wire:click="agregarVideo" wire:loading.attr="disabled">
                            Agregar video
                        </x-primary-button>
                    </div>
                </div>
            </div>

            {{-- ─── Banco de preguntas ────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Preguntas</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Umbral de aprobación configurado: {{ $umbralAprobacion }}/10. Necesitas al menos
                        {{ $umbralAprobacion }} {{ Str::plural('pregunta', $umbralAprobacion) }} para que la evaluación tenga sentido.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($preguntas as $pregunta)
                        <div class="p-5 flex items-start gap-4">
                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-100 text-xs font-medium text-gray-500 shrink-0 mt-0.5">
                                {{ $pregunta->orden }}
                            </span>
                            <div class="flex-1 min-w-0 space-y-2">
                                <p class="text-sm text-gray-800">{{ $pregunta->enunciado }}</p>

                                @if ($pregunta->tipo === 'opciones')
                                    <ul class="space-y-1">
                                        @foreach ($pregunta->opciones as $opcion)
                                            <li class="text-xs flex items-center gap-2 {{ $opcion->es_correcta ? 'text-green-700 font-medium' : 'text-gray-500' }}">
                                                <span>{{ $opcion->es_correcta ? '✓' : '·' }}</span>
                                                {{ $opcion->texto }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $pregunta->respuesta_correcta ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                        {{ $pregunta->respuesta_correcta ? 'Verdadero' : 'Falso' }}
                                    </span>
                                @endif
                            </div>
                            <x-danger-button wire:click="deletePregunta({{ $pregunta->id }})" wire:confirm="¿Eliminar esta pregunta?">
                                Eliminar
                            </x-danger-button>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            Todavía no hay preguntas en este módulo.
                        </div>
                    @endforelse
                </div>

                <div class="p-6 bg-gray-50 space-y-4">
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="radio" wire:model.live="tipoPregunta" value="vf" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                            Verdadero / Falso
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="radio" wire:model.live="tipoPregunta" value="opciones" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                            Opción múltiple
                        </label>
                    </div>

                    <div>
                        <x-input-label for="enunciado" value="Enunciado" />
                        <textarea wire:model="enunciado" id="enunciado" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" placeholder="Escribe el enunciado..."></textarea>
                        <x-input-error :messages="$errors->get('enunciado')" class="mt-1" />
                    </div>

                    @if ($tipoPregunta === 'vf')
                        <div class="flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input type="radio" wire:model="respuestaCorrecta" value="1" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                                Verdadero
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                <input type="radio" wire:model="respuestaCorrecta" value="0" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                                Falso
                            </label>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach ($opciones as $i => $opcion)
                                <div class="flex items-center gap-2">
                                    <input type="radio" wire:model="opcionCorrecta" value="{{ $i }}" class="border-gray-300 text-brand-600 focus:ring-brand-500 shrink-0" title="Marcar como correcta">
                                    <x-text-input wire:model="opciones.{{ $i }}" class="w-full" placeholder="Opción {{ $i + 1 }}" />
                                    @if (count($opciones) > 2)
                                        <button type="button" wire:click="removeOpcion({{ $i }})" class="text-gray-400 hover:text-red-600 text-sm shrink-0">✕</button>
                                    @endif
                                </div>
                            @endforeach
                            <x-input-error :messages="$errors->get('opciones.*')" class="mt-1" />
                            <x-input-error :messages="$errors->get('opcionCorrecta')" class="mt-1" />

                            <button type="button" wire:click="addOpcion" class="text-sm text-brand-600 hover:text-brand-800 font-medium">
                                + Agregar opción
                            </button>
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <x-primary-button wire:click="addPregunta" wire:loading.attr="disabled">
                            Agregar pregunta
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
