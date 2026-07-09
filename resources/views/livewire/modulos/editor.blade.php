<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('modulos.index') }}" wire:navigate class="text-gray-400 hover:text-gray-600">←</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $modulo->titulo }}
                </h2>
            </div>
            <a href="{{ route('modulos.seguimiento', $modulo) }}" wire:navigate class="text-sm font-medium text-brand-600 hover:text-brand-800">
                Ver seguimiento →
            </a>
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
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
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
                    <x-input-label for="contenido" value="Contenido" />
                    <x-rich-editor model="contenido" :value="$contenido" class="rounded-lg border border-gray-300 overflow-hidden" />
                    <x-input-error :messages="$errors->get('contenido')" class="mt-1" />
                </div>

                <div class="flex flex-wrap items-end gap-6">
                    <div class="max-w-xs">
                        <x-input-label for="umbralAprobacion" value="Umbral de aprobación (sobre 10)" />
                        <x-text-input wire:model="umbralAprobacion" id="umbralAprobacion" type="number" min="1" max="10" class="w-full" />
                        <x-input-error :messages="$errors->get('umbralAprobacion')" class="mt-1" />
                    </div>

                    <div class="max-w-xs">
                        <x-input-label for="fechaLimite" value="Fecha límite (opcional)" />
                        <x-text-input wire:model="fechaLimite" id="fechaLimite" type="date" class="w-full" />
                        <x-input-error :messages="$errors->get('fechaLimite')" class="mt-1" />
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 pb-2">
                        <input type="checkbox" wire:model="activo" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Módulo activo
                    </label>
                </div>

                <div class="flex justify-end pt-2">
                    <x-primary-button wire:click="save" wire:loading.attr="disabled">
                        Guardar cambios
                    </x-primary-button>
                </div>
            </div>

            {{-- ─── Materiales (diapositivas / PDF) ───────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Materiales</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Las imágenes se muestran incrustadas dentro del contenido cuando el colaborador lee el
                        módulo. Los PDF y PPT solo quedan disponibles como descarga, no se muestran incrustados.
                    </p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse ($archivos as $archivo)
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
                            Todavía no hay materiales en este módulo.
                        </div>
                    @endforelse
                </div>

                <div class="p-6 bg-gray-50 space-y-3">
                    <x-input-label for="nuevosArchivos" value="Subir diapositivas, PDF o imágenes" />
                    <input
                        type="file"
                        wire:model="nuevosArchivos"
                        id="nuevosArchivos"
                        multiple
                        accept=".pdf,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.gif"
                        class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100"
                    >
                    <p class="text-xs text-gray-400">PDF, PPT, PPTX, JPG, PNG, WEBP o GIF, máximo 25 MB por archivo.</p>
                    <x-input-error :messages="$errors->get('nuevosArchivos.*')" class="mt-1" />

                    <div wire:loading wire:target="nuevosArchivos" class="text-xs text-gray-400">
                        Subiendo...
                    </div>

                    @if (! empty($nuevosArchivos))
                        <x-primary-button wire:click="subirArchivos" wire:loading.attr="disabled">
                            Guardar materiales
                        </x-primary-button>
                    @endif
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
