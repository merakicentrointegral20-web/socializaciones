<div>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mis-modulos.show', $modulo) }}" wire:navigate class="text-gray-400 hover:text-gray-600">←</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Evaluación · {{ $modulo->titulo }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-4 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                <p class="text-sm text-gray-500">{{ $preguntas->count() }} {{ Str::plural('pregunta', $preguntas->count()) }}</p>
                <p class="text-sm text-gray-500">Necesitas <span class="font-medium text-gray-700">{{ $modulo->umbral_aprobacion }}/10</span> para aprobar</p>
            </div>

            <x-input-error :messages="$errors->get('respuestas')" />

            @foreach ($preguntas as $i => $pregunta)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-3">
                    <p class="text-sm font-medium text-gray-800">
                        <span class="text-gray-400">{{ $i + 1 }}.</span> {{ $pregunta->enunciado }}
                    </p>

                    @if ($pregunta->tipo === 'opciones')
                        <div class="space-y-2">
                            @foreach ($pregunta->opciones as $opcion)
                                <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-2.5 cursor-pointer transition has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50">
                                    <input type="radio" wire:model="respuestas.{{ $pregunta->id }}" value="{{ $opcion->id }}" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                                    <span class="text-sm text-gray-700">{{ $opcion->texto }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="flex gap-3">
                            <label class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 cursor-pointer transition has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50">
                                <input type="radio" wire:model="respuestas.{{ $pregunta->id }}" value="1" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-700">Verdadero</span>
                            </label>
                            <label class="flex-1 flex items-center justify-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 cursor-pointer transition has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50">
                                <input type="radio" wire:model="respuestas.{{ $pregunta->id }}" value="0" class="border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span class="text-sm text-gray-700">Falso</span>
                            </label>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="flex justify-end pb-4">
                <x-primary-button wire:click="enviar" wire:loading.attr="disabled">
                    Enviar evaluación
                </x-primary-button>
            </div>
        </div>
    </div>
</div>
