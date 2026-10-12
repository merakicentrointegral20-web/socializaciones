<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Personal
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ─── Resumen ─────────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalConCuenta }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Con cuenta</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalInstructores }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Instructores</p>
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

            {{-- ─── Buscador ───────────────────────────────────────────────────── --}}
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Buscar por nombre o correo..."
                class="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
            >

            {{-- ─── Personal ───────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">Personal <span class="text-gray-400 font-normal">({{ $totalConCuenta }})</span></h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Cuentas, cédula, celular y área se administran desde Xuri Apex — acá solo se muestran, en solo lectura.
                        Lo único que se gestiona desde xr-rrhh es quién es instructor (crea módulos y capacitaciones)
                        y quién es solo supervisión (no sale en actas, seguimiento ni pendientes).
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Nombre</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Cédula</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Área</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Celular</th>
                                <th class="px-6 py-3.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Instructor</th>
                                <th class="px-6 py-3.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Solo supervisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($people as $person)
                                @php
                                    $esInstructor = $person->hasRole($instructorRole);
                                    $esSupervision = $person->roles->contains('name', $supervisionRole);
                                    $areaRoles = $person->roles->pluck('name')->reject(fn ($r) => in_array($r, [$instructorRole, $supervisionRole]));
                                @endphp
                                <tr class="odd:bg-white even:bg-gray-50/60 hover:bg-brand-50/50 transition-colors duration-150">
                                    <td class="px-6 py-4 align-middle border-b border-gray-100">
                                        <p class="font-semibold text-gray-900 truncate">{{ $person->name }}</p>
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $person->email }}</p>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-gray-600 whitespace-nowrap border-b border-gray-100">
                                        {{ $person->id_card ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 align-middle text-gray-600 whitespace-nowrap border-b border-gray-100">
                                        @if ($person->collaborator)
                                            <span class="block text-[11px] text-gray-400">{{ $person->collaborator->code }}</span>
                                            {{ $person->collaborator->function ?? '—' }}
                                        @elseif ($areaRoles->isNotEmpty())
                                            {{ $areaRoles->map(fn ($r) => \App\Livewire\Colaboradores\GestionAcceso::ROLE_LABELS[$r] ?? $r)->implode(', ') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-middle text-gray-600 whitespace-nowrap border-b border-gray-100">
                                        {{ $person->phone ?: '—' }}
                                    </td>
                                    <td class="px-6 py-4 align-middle border-b border-gray-100">
                                        <div class="flex justify-center">
                                            <x-toggle-switch wire:click="askToggleInstructor({{ $person->id }})" :on="$esInstructor" title="Instructor para {{ $person->name }}" class="p-3 -m-3 rounded-lg hover:bg-white/80" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle border-b border-gray-100">
                                        <div class="flex justify-center">
                                            <x-toggle-switch wire:click="toggleSupervision({{ $person->id }})" :on="$esSupervision" title="Solo supervisión para {{ $person->name }}" class="p-3 -m-3 rounded-lg hover:bg-white/80" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-10 text-center text-sm text-gray-400">No se encontraron cuentas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($people->hasPages())
                    <div class="p-5 border-t border-gray-100">
                        {{ $people->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Confirmación antes de asignar/quitar el rol de instructor ─────── --}}
    <x-modal name="confirm-toggle" :show="$confirmingToggle !== null" focusable>
        @if ($confirmingToggle)
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $confirmingToggle['willActivate'] ? '¿Asignar rol de instructor?' : '¿Quitar rol de instructor?' }}
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    @if ($confirmingToggle['willActivate'])
                        <strong>{{ $confirmingToggle['userName'] }}</strong> va a poder crear módulos y gestionar capacitaciones.
                    @else
                        <strong>{{ $confirmingToggle['userName'] }}</strong> ya no va a poder crear módulos ni gestionar capacitaciones.
                    @endif
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button wire:click="cancelToggle">
                        Cancelar
                    </x-secondary-button>
                    <x-primary-button wire:click="applyToggle" wire:loading.attr="disabled">
                        Confirmar
                    </x-primary-button>
                </div>
            </div>
        @endif
    </x-modal>
</div>
