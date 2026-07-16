<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Módulos
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Buscar por título o categoría..."
                    class="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                >
                <x-primary-button wire:click="toggleCreateForm">
                    {{ $showCreateForm ? 'Cancelar' : '+ Crear módulo' }}
                </x-primary-button>
            </div>

            @if ($showCreateForm)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                    <h3 class="font-medium text-gray-900">Nuevo módulo</h3>

                    <div>
                        <x-input-label for="titulo" value="Título" />
                        <x-text-input wire:model="titulo" id="titulo" class="w-full" placeholder="Ej. Inducción de seguridad industrial" />
                        <x-input-error :messages="$errors->get('titulo')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="categoria" value="Categoría" />
                        <div class="flex gap-2">
                            <select wire:model="categoria" id="categoria" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Selecciona una categoría</option>
                                @foreach ($categorias as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="$toggle('mostrandoNuevaCategoria')" class="shrink-0 text-sm text-brand-600 hover:text-brand-700 px-2 whitespace-nowrap">
                                + Nueva
                            </button>
                        </div>
                        @if ($mostrandoNuevaCategoria)
                            <div class="mt-2 flex gap-2">
                                <x-text-input wire:model="nuevaCategoria" placeholder="Nombre de la categoría nueva" class="w-full" />
                                <x-secondary-button type="button" wire:click="agregarCategoria">Agregar</x-secondary-button>
                            </div>
                            <x-input-error :messages="$errors->get('nuevaCategoria')" class="mt-1" />
                        @endif
                        <x-input-error :messages="$errors->get('categoria')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="contenido" value="Contenido (opcional por ahora)" />
                        <x-rich-editor model="contenido" :value="$contenido" class="rounded-lg border border-gray-300 overflow-hidden" />
                        <x-input-error :messages="$errors->get('contenido')" class="mt-1" />
                    </div>

                    <div class="flex flex-wrap gap-6">
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
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-secondary-button wire:click="toggleCreateForm">Cancelar</x-secondary-button>
                        <x-primary-button wire:click="create" wire:loading.attr="disabled">Crear módulo</x-primary-button>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @forelse ($modulos as $modulo)
                        <a href="{{ route('modulos.show', $modulo) }}" wire:navigate class="p-5 flex flex-wrap items-center gap-4 hover:bg-gray-50 transition">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-900 truncate">{{ $modulo->titulo }}</p>
                                    @unless ($modulo->activo)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Inactivo</span>
                                    @endunless
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $modulo->categoria }} · {{ $modulo->preguntas_count }} {{ Str::plural('pregunta', $modulo->preguntas_count) }} · creado por {{ $modulo->creador->name }}
                                </p>
                            </div>
                            <span class="text-brand-600 text-sm font-medium">Editar →</span>
                        </a>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            Todavía no has creado ningún módulo.
                        </div>
                    @endforelse
                </div>

                @if ($modulos->hasPages())
                    <div class="p-5 border-t border-gray-100">
                        {{ $modulos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
