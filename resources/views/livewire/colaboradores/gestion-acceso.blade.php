<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Personal y Colaboradores
            </h2>
            <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'modal-crear-colaborador')" wire:click="abrirModalCrear">
                + Nuevo Colaborador
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ─── Resumen ─────────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5 relative overflow-hidden group hover:border-brand-300 transition">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-700 to-brand-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-extrabold text-brand-950">{{ $totalConCuenta }}</p>
                            <p class="text-xs font-medium text-gray-500 mt-1">Colaboradores registrados</p>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5 relative overflow-hidden group hover:border-accent-300 transition">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-accent-500 to-accent-400"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-extrabold text-accent-700">{{ $totalInstructores }}</p>
                            <p class="text-xs font-medium text-gray-500 mt-1">Instructores con acceso a gestión</p>
                        </div>
                        <div class="h-11 w-11 rounded-xl bg-accent-50 text-accent-600 flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ─── Buscador y Acciones ─────────────────────────────────────────── --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Buscar por nombre, correo o cédula..."
                    class="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-brand-600 focus:ring-accent-400"
                >
                <x-primary-button type="button" x-data x-on:click="$dispatch('open-modal', 'modal-crear-colaborador')" wire:click="abrirModalCrear">
                    + Nuevo Colaborador
                </x-primary-button>
            </div>

            {{-- ─── Tabla Personal ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-purple-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 flex items-center gap-2">
                            <span>Nómina de {{ config('xr_rrhh.empresa_nombre', 'MERAKI') }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">{{ $totalConCuenta }}</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Administra los accesos, instructores y roles de los colaboradores para las capacitaciones.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-purple-50/70 border-b border-purple-200/70">
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold text-brand-900 uppercase tracking-wider border-b border-purple-100">Nombre</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold text-brand-900 uppercase tracking-wider border-b border-purple-100">Cédula</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold text-brand-900 uppercase tracking-wider border-b border-purple-100">Cargo / Área</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-bold text-brand-900 uppercase tracking-wider border-b border-purple-100">Celular</th>
                                <th class="px-6 py-3.5 text-center text-[11px] font-bold text-brand-900 uppercase tracking-wider border-b border-purple-100">Estado</th>
                                <th class="px-6 py-3.5 text-center text-[11px] font-bold text-brand-900 uppercase tracking-wider border-b border-purple-100">Instructor</th>
                                <th class="px-6 py-3.5 text-center text-[11px] font-bold text-brand-900 uppercase tracking-wider border-b border-purple-100">Solo supervisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($people as $person)
                                @php
                                    $esInstructor = $person->hasRole($instructorRole);
                                    $esSupervision = $person->roles->contains('name', $supervisionRole);
                                    $cargo = $person->collaborator?->function ?: 'General';
                                @endphp
                                <tr class="odd:bg-white even:bg-purple-50/20 hover:bg-brand-50/50 transition-colors duration-150">
                                    <td class="px-6 py-4 align-middle border-b border-purple-50">
                                        <p class="font-semibold text-gray-900 truncate">{{ $person->name }}</p>
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $person->email }}</p>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-gray-600 whitespace-nowrap border-b border-purple-50">
                                        {{ $person->id_card ?: ($person->collaborator?->id_card ?: '—') }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-gray-600 border-b border-purple-50">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-50 text-brand-800 border border-purple-100/80">
                                            {{ $cargo }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-gray-600 whitespace-nowrap border-b border-purple-50">
                                        {{ $person->phone ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-center border-b border-purple-50">
                                        <button
                                            wire:click="toggleEstadoCuenta({{ $person->id }})"
                                            type="button"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold cursor-pointer transition-colors {{ $person->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200 border border-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200 border border-red-200' }}"
                                            title="Click para cambiar estado"
                                        >
                                            {{ $person->is_active ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-center border-b border-purple-50">
                                        <button
                                            wire:click="askToggleInstructor({{ $person->id }})"
                                            type="button"
                                            class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold transition-colors {{ $esInstructor ? 'bg-brand-50 text-brand-700 hover:bg-brand-100 border border-brand-200 shadow-xs' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100' }}"
                                        >
                                            {{ $esInstructor ? '✓ Instructor' : 'Habilitar' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-center border-b border-purple-50">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleSupervision({{ $person->id }})"
                                            {{ $esSupervision ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-brand-700 focus:ring-accent-400 cursor-pointer"
                                            title="Marcar como solo supervisión"
                                        >
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                                        No se encontraron colaboradores. Usa el botón "+ Nuevo Colaborador" para registrar uno.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($people->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $people->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ─── Modal Crear Colaborador ─────────────────────────────────────────────── --}}
    <x-modal name="modal-crear-colaborador" :show="$mostrandoModalCrear" focusable>
        <form wire:submit="guardarColaborador" class="p-6">
            <div class="flex items-center gap-2.5">
                <span class="h-2.5 w-2.5 rounded-full bg-accent-500"></span>
                <h2 class="text-lg font-bold text-brand-950">
                    Registrar Nuevo Colaborador
                </h2>
            </div>
            <p class="mt-1 text-sm text-gray-600">
                Crea la cuenta de usuario para que el colaborador pueda ingresar a sus capacitaciones en {{ config('xr_rrhh.empresa_nombre', 'MERAKI') }}.
            </p>

            <div class="mt-6 space-y-4">
                <div>
                    <x-input-label for="nuevoNombre" value="Nombre y Apellido *" />
                    <x-text-input wire:model="nuevoNombre" id="nuevoNombre" type="text" class="mt-1 block w-full" placeholder="Ej. Juan Pérez" required />
                    <x-input-error :messages="$errors->get('nuevoNombre')" class="mt-1" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="nuevoEmail" value="Correo electrónico *" />
                        <x-text-input wire:model="nuevoEmail" id="nuevoEmail" type="email" class="mt-1 block w-full" placeholder="juanperez@meraki.com" required />
                        <x-input-error :messages="$errors->get('nuevoEmail')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="nuevaCedula" value="Cédula de Identidad / DNI" />
                        <x-text-input wire:model="nuevaCedula" id="nuevaCedula" type="text" class="mt-1 block w-full" placeholder="0912345678" />
                        <x-input-error :messages="$errors->get('nuevaCedula')" class="mt-1" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="nuevoTelefono" value="Celular / WhatsApp" />
                        <x-text-input wire:model="nuevoTelefono" id="nuevoTelefono" type="text" class="mt-1 block w-full" placeholder="0991234567" />
                        <x-input-error :messages="$errors->get('nuevoTelefono')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="nuevoCargo" value="Cargo / Área" />
                        <x-text-input wire:model="nuevoCargo" id="nuevoCargo" type="text" class="mt-1 block w-full" placeholder="Ej. Operaciones, Ventas, Logística" />
                        <x-input-error :messages="$errors->get('nuevoCargo')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="nuevoPassword" value="Contraseña de acceso (opcional, por defecto: password)" />
                    <x-text-input wire:model="nuevoPassword" id="nuevoPassword" type="text" class="mt-1 block w-full" placeholder="password" />
                    <x-input-error :messages="$errors->get('nuevoPassword')" class="mt-1" />
                </div>

                <div class="pt-2 border-t border-gray-100 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="esInstructor" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Asignar rol de <strong>Instructor</strong> (puede crear módulos y capacitaciones)</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="esSupervision" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm text-gray-700">Asignar rol de <strong>Solo Supervisión</strong> (no rinde exámenes ni sale en actas)</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-data x-on:click="$dispatch('close-modal', 'modal-crear-colaborador')" wire:click="cerrarModalCrear">
                    Cancelar
                </x-secondary-button>

                <x-primary-button wire:loading.attr="disabled">
                    Guardar Colaborador
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- ─── Modal Confirmar Toggle Instructor ───────────────────────────────────── --}}
    <x-modal name="confirm-toggle" :show="(bool) $confirmingToggle" focusable>
        @if ($confirmingToggle)
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ $confirmingToggle['willActivate'] ? 'Habilitar' : 'Quitar' }} rol de instructor
                </h2>
                <p class="mt-2 text-sm text-gray-500">
                    ¿Estás seguro de que deseas {{ $confirmingToggle['willActivate'] ? 'asignar el rol de instructor a' : 'remover el rol de instructor de' }}
                    <strong>{{ $confirmingToggle['userName'] }}</strong>?
                    {{ $confirmingToggle['willActivate'] ? 'Podrá crear módulos, programar inducciones y firmar actas.' : 'Ya no podrá crear ni editar capacitaciones.' }}
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button wire:click="cancelToggle">
                        Cancelar
                    </x-secondary-button>

                    <x-primary-button wire:click="applyToggle">
                        Confirmar
                    </x-primary-button>
                </div>
            </div>
        @endif
    </x-modal>
</div>
