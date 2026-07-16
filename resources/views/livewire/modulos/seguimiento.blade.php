<div>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('modulos.show', $modulo) }}" wire:navigate class="text-gray-400 hover:text-gray-600">←</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Seguimiento · {{ $modulo->titulo }}
                @if ($modulo->fecha_limite)
                    <span class="text-sm font-normal text-gray-400">(fecha límite: {{ $modulo->fecha_limite->format('d/m/Y') }})</span>
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <select wire:model.live="filtro" class="rounded-lg border-gray-300 text-sm text-gray-600 focus:border-brand-500 focus:ring-brand-500">
                        @foreach (\App\Livewire\Modulos\Seguimiento::FILTROS as $valor => $etiqueta)
                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ count($seleccionados) }} {{ Str::plural('persona', count($seleccionados)) }} seleccionada(s) para inducción.
                    </p>
                    <x-input-error :messages="$errors->get('seleccionados')" class="mt-1" />
                </div>
                <x-primary-button wire:click="abrirModalInduccion" wire:loading.attr="disabled">
                    Agendar inducción
                </x-primary-button>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-xs uppercase tracking-wide text-gray-400">
                        <tr>
                            <th class="p-4 w-10"></th>
                            <th class="p-4 text-left font-medium">Nombre</th>
                            <th class="p-4 text-left font-medium">Leyó el módulo</th>
                            <th class="p-4 text-left font-medium">Inducción</th>
                            <th class="p-4 text-left font-medium">Evaluación</th>
                            <th class="p-4 text-left font-medium">Certificado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($filas as $fila)
                            @php
                                $puedeSeleccionar = $fila->lectura?->leido_at && ! $fila->induccion;
                                $motivoDeshabilitado = match (true) {
                                    ! $fila->lectura?->leido_at => 'Todavía no leyó el módulo',
                                    (bool) $fila->induccion => 'Ya tiene una inducción agendada',
                                    default => '',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/60">
                                <td class="p-4">
                                    <input
                                        type="checkbox"
                                        wire:model="seleccionados"
                                        value="{{ $fila->usuario->id }}"
                                        {{ $puedeSeleccionar ? '' : 'disabled' }}
                                        title="{{ $motivoDeshabilitado }}"
                                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 disabled:opacity-30"
                                    >
                                </td>
                                <td class="p-4 text-gray-700">{{ $fila->usuario->name }}</td>
                                <td class="p-4">
                                    @if ($fila->lectura?->leido_at)
                                        <span class="text-xs text-gray-500">{{ $fila->lectura->leido_at->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-xs text-gray-300">Sin leer</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if ($fila->induccion)
                                        <span class="text-xs text-gray-500">{{ $fila->induccion->capacitacion->fecha->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span class="text-xs text-gray-300">Sin agendar</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if ($fila->evaluacion)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $fila->evaluacion->aprobado ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                            {{ $fila->evaluacion->score }}/{{ $fila->evaluacion->total_preguntas }} {{ $fila->evaluacion->aprobado ? 'Aprobó' : 'No aprobó' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-300">Pendiente</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if ($fila->certificado)
                                        @if ($fila->certificado->pdf_path)
                                            <a href="{{ route('certificados.pdf', $fila->certificado) }}" target="_blank" class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700 hover:bg-purple-100">
                                                {{ $fila->certificado->emitido_at?->format('d/m/Y') }} · PDF
                                            </a>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-700">
                                                {{ $fila->certificado->emitido_at?->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-sm text-gray-400">Nadie coincide con este filtro.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Inducciones agendadas</h3>
                    <p class="text-xs text-gray-400 mt-1">
                        Después de la fecha puedes registrar quién asistió y subir el acta.
                    </p>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse ($capacitaciones as $capacitacion)
                        @php $yaPaso = $capacitacion->fecha->isPast(); @endphp
                        <div class="p-5 flex flex-wrap items-center gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-700">
                                    {{ $capacitacion->fecha->format('d/m/Y H:i') }}
                                    @if ($capacitacion->modalidad) <span class="text-gray-400">· {{ $capacitacion->modalidad }}</span> @endif
                                    @if ($capacitacion->lugar) <span class="text-gray-400">· {{ $capacitacion->lugar }}</span> @endif
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $capacitacion->asistentes->count() }} {{ Str::plural('persona', $capacitacion->asistentes->count()) }} invitada(s)
                                    @if ($capacitacion->acta_path)
                                        · {{ $capacitacion->asistentes->where('asistio', true)->count() }} confirmada(s)
                                    @endif
                                </p>
                            </div>

                            @if ($capacitacion->acta_path)
                                <a href="{{ route('capacitaciones.acta', [$modulo, $capacitacion]) }}" target="_blank" class="text-sm text-brand-600 hover:text-brand-800">
                                    Ver acta
                                </a>
                            @else
                                <a href="{{ route('capacitaciones.acta-imprimir', [$modulo, $capacitacion]) }}" target="_blank" class="text-sm text-brand-600 hover:text-brand-800">
                                    Imprimir acta
                                </a>
                                <x-secondary-button wire:click="abrirModalReagendar({{ $capacitacion->id }})">
                                    Reagendar
                                </x-secondary-button>
                            @endif

                            @if ($yaPaso)
                                <x-secondary-button wire:click="abrirModalAsistencia({{ $capacitacion->id }})">
                                    {{ $capacitacion->acta_path ? 'Editar asistencia' : 'Marcar asistencia' }}
                                </x-secondary-button>
                            @else
                                <span class="text-xs text-gray-300">Disponible después de la fecha</span>
                            @endif
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            Todavía no hay inducciones agendadas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-modal name="agendar-induccion" focusable>
        <div class="p-6 space-y-4">
            <h3 class="font-medium text-gray-900">
                Agendar inducción para {{ count($seleccionados) }} {{ Str::plural('persona', count($seleccionados)) }}
            </h3>

            <div>
                <x-input-label for="fecha" value="Fecha y hora" />
                <x-text-input wire:model="fecha" id="fecha" type="datetime-local" class="w-full" />
                <x-input-error :messages="$errors->get('fecha')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="modalidad" value="Modalidad (opcional)" />
                <x-text-input wire:model="modalidad" id="modalidad" class="w-full" placeholder="Presencial, virtual..." />
                <x-input-error :messages="$errors->get('modalidad')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="lugar" value="Lugar (opcional)" />
                <x-text-input wire:model="lugar" id="lugar" class="w-full" placeholder="Sala de capacitación, link de reunión..." />
                <x-input-error :messages="$errors->get('lugar')" class="mt-1" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button wire:click="cancelarInduccion">Cancelar</x-secondary-button>
                <x-primary-button wire:click="agendarInduccion" wire:loading.attr="disabled">Agendar</x-primary-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="marcar-asistencia" focusable>
        <div class="p-6 space-y-4">
            @php $capacitacionActual = $capacitaciones->firstWhere('id', $capacitacionEditando); @endphp
            <h3 class="font-medium text-gray-900">Marcar asistencia</h3>

            @if ($capacitacionActual)
                <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-100 rounded-lg p-3">
                    @foreach ($capacitacionActual->asistentes as $asistente)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" wire:model="asistieron" value="{{ $asistente->user_id }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            {{ $asistente->user->name }}
                        </label>
                    @endforeach
                </div>
            @endif

            <div>
                <x-input-label for="actaArchivo" value="Acta de inducción (imagen)" />
                @if ($capacitacionActual?->acta_path)
                    <p class="text-xs text-gray-400 mb-1">Ya hay un acta subida. Solo sube una nueva si quieres reemplazarla.</p>
                @endif
                <input
                    type="file"
                    wire:model="actaArchivo"
                    id="actaArchivo"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100"
                >
                <x-input-error :messages="$errors->get('actaArchivo')" class="mt-1" />
                <div wire:loading wire:target="actaArchivo" class="text-xs text-gray-400 mt-1">Subiendo...</div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button wire:click="cancelarAsistencia">Cancelar</x-secondary-button>
                <x-primary-button wire:click="guardarAsistencia" wire:loading.attr="disabled">Guardar asistencia</x-primary-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="reagendar-induccion" focusable>
        <div class="p-6 space-y-4">
            <h3 class="font-medium text-gray-900">Reagendar inducción</h3>

            <div>
                <x-input-label for="fechaReagendar" value="Fecha y hora" />
                <x-text-input wire:model="fechaReagendar" id="fechaReagendar" type="datetime-local" class="w-full" />
                <x-input-error :messages="$errors->get('fechaReagendar')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="modalidadReagendar" value="Modalidad (opcional)" />
                <x-text-input wire:model="modalidadReagendar" id="modalidadReagendar" class="w-full" placeholder="Presencial, virtual..." />
                <x-input-error :messages="$errors->get('modalidadReagendar')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="lugarReagendar" value="Lugar (opcional)" />
                <x-text-input wire:model="lugarReagendar" id="lugarReagendar" class="w-full" placeholder="Sala de capacitación, link de reunión..." />
                <x-input-error :messages="$errors->get('lugarReagendar')" class="mt-1" />
            </div>

            <div>
                <x-input-label value="Agregar colaboradores olvidados (opcional)" />
                <p class="text-xs text-gray-400 mb-1">
                    Solo aparece quien ya leyó el módulo y todavía no tiene inducción agendada. Los que ya están en esta sesión no se ven afectados.
                </p>
                @if ($candidatosParaAgregar->isEmpty())
                    <p class="text-xs text-gray-300">No hay nadie pendiente por agregar.</p>
                @else
                    <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-100 rounded-lg p-3">
                        @foreach ($candidatosParaAgregar as $candidato)
                            <label class="flex items-center gap-2 text-sm text-gray-600">
                                <input type="checkbox" wire:model="seleccionadosParaAgregar" value="{{ $candidato->id }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                {{ $candidato->name }}
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between gap-3 pt-2">
                <button
                    type="button"
                    wire:click="eliminarInduccion"
                    wire:confirm="¿Eliminar esta inducción? Se perderá el agendamiento y los invitados quedarán libres para que se les agende una nueva."
                    class="text-xs text-gray-300 hover:text-red-600"
                >
                    Eliminar inducción
                </button>
                <div class="flex gap-3">
                    <x-secondary-button wire:click="cancelarReagendar">Cancelar</x-secondary-button>
                    <x-primary-button wire:click="guardarReagendamiento" wire:loading.attr="disabled">Guardar fecha</x-primary-button>
                </div>
            </div>
        </div>
    </x-modal>
</div>
