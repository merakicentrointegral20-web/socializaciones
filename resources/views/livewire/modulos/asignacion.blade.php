<div>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('modulos.show', $modulo) }}" wire:navigate class="text-gray-400 hover:text-gray-600">←</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Asignación · {{ $modulo->titulo }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-sm text-gray-700">
                        Elige a quién le llega este módulo en "Mis módulos". Si no lo asignas, no lo verá nadie.
                    </p>
                    <a href="{{ route('modulos.asignacion.acta', $modulo) }}" target="_blank" class="block sm:inline-block">
                        <x-secondary-button type="button" class="w-full sm:w-auto justify-center shrink-0">
                            🖨 Imprimir acta
                        </x-secondary-button>
                    </a>
                </div>

                <label class="flex items-center gap-2 text-sm font-medium text-gray-800 rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <input type="checkbox" wire:model.live="todos" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    Todos los colaboradores
                </label>

                @if (! $todos)
                    <div class="space-y-3">
                        <x-text-input wire:model.live="search" placeholder="Buscar por nombre o correo..." class="w-full" />
                        <x-input-error :messages="$errors->get('seleccionados')" class="mt-1" />

                        <p class="text-xs text-gray-400">
                            {{ count($seleccionados) }} {{ Str::plural('colaborador', count($seleccionados)) }} seleccionado(s).
                        </p>

                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-50 border border-gray-100 rounded-xl">
                            @forelse ($colaboradores as $colaborador)
                                <label class="flex items-center gap-3 p-3 text-sm text-gray-700 hover:bg-gray-50/60">
                                    <input type="checkbox" wire:model="seleccionados" value="{{ $colaborador->id }}" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate">{{ $colaborador->name }}</span>
                                        <span class="block text-xs text-gray-400 truncate">{{ $colaborador->email }}</span>
                                    </span>
                                </label>
                            @empty
                                <div class="p-8 text-center text-sm text-gray-400">Nadie coincide con la búsqueda.</div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <div class="flex pt-2">
                    <x-primary-button wire:click="guardar" wire:loading.attr="disabled" class="w-full sm:w-auto sm:ml-auto">
                        Guardar asignación
                    </x-primary-button>
                </div>
            </div>
        </div>
    </div>
</div>
