<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Personal y acceso
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <p class="text-sm text-gray-500 max-w-2xl">
                Toda la nómina que ya tiene cuenta aparece abajo con su rol. Desde aquí activas o quitas
                el rol de <strong>instructor</strong> (puede crear módulos y gestionar capacitaciones) y quién
                puede gestionar el acceso de los demás. Los colaboradores de bodega/CEDI que todavía no
                tienen cuenta aparecen aparte, para activarles el acceso por primera vez.
            </p>

            @if (session('access-granted'))
                <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5">
                    <p class="text-sm font-medium text-brand-900">Acceso activado</p>
                    <p class="mt-1 text-sm text-brand-800">{{ session('access-granted') }}</p>
                    <p class="mt-2 text-xs text-brand-700">
                        Esta contraseña no se vuelve a mostrar. Compártela con la persona por un canal seguro.
                    </p>
                </div>
            @endif

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

            <div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Buscar por nombre o correo..."
                    class="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                >
            </div>

            {{-- ─── Personal con cuenta ────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <h3 class="font-medium text-gray-900">Personal con cuenta</h3>
                    <x-secondary-button wire:click="toggleNewForm">
                        {{ $showNewForm ? 'Cancelar' : '+ Agregar colaborador' }}
                    </x-secondary-button>
                </div>

                @if ($showNewForm)
                    <div class="p-5 border-b border-gray-100 bg-gray-50 space-y-4">
                        <div class="flex gap-2">
                            <button
                                type="button"
                                wire:click="$set('newArea', 'administracion')"
                                class="flex-1 rounded-lg border px-4 py-2.5 text-sm font-medium transition {{ $newArea === 'administracion' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 text-gray-500 hover:bg-gray-100' }}"
                            >
                                Administración
                                <span class="block text-[11px] font-normal opacity-75">Solo crea la cuenta acá</span>
                            </button>
                            <button
                                type="button"
                                wire:click="$set('newArea', 'produccion')"
                                class="flex-1 rounded-lg border px-4 py-2.5 text-sm font-medium transition {{ $newArea === 'produccion' ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 text-gray-500 hover:bg-gray-100' }}"
                            >
                                Producción
                                <span class="block text-[11px] font-normal opacity-75">También se refleja en XURI APEX</span>
                            </button>
                        </div>

                        <div class="flex flex-wrap items-end gap-3">
                            <div>
                                <x-input-label for="newName" value="Nombre completo" />
                                <x-text-input wire:model="newName" id="newName" class="w-56" placeholder="Nombre Apellido" />
                                <x-input-error :messages="$errors->get('newName')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="newCedula" value="{{ $newArea === 'produccion' ? 'Cédula (obligatoria)' : 'Cédula' }}" />
                                <x-text-input wire:model="newCedula" id="newCedula" class="w-40" placeholder="0102030405" />
                                <x-input-error :messages="$errors->get('newCedula')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="newTelefono" value="Celular (WhatsApp)" />
                                <x-text-input wire:model="newTelefono" id="newTelefono" class="w-40" placeholder="0991234567" />
                                <x-input-error :messages="$errors->get('newTelefono')" class="mt-1" />
                            </div>

                            @if ($newArea === 'produccion')
                                <div>
                                    <x-input-label for="newFuncionProduccion" value="Función" />
                                    <select wire:model="newFuncionProduccion" id="newFuncionProduccion" class="w-56 rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                                        <option value="">Selecciona...</option>
                                        @foreach ($funcionesProduccion as $funcion)
                                            <option value="{{ $funcion }}">{{ $funcion }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('newFuncionProduccion')" class="mt-1" />
                                </div>
                            @else
                                <div>
                                    <x-input-label for="newCargoAdministracion" value="Cargo (opcional)" />
                                    <x-text-input wire:model="newCargoAdministracion" id="newCargoAdministracion" class="w-56" placeholder="Ej. Analista de RRHH" />
                                </div>
                            @endif

                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 pb-2">
                                <input type="checkbox" wire:model="newAsInstructor" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                Es instructor
                            </label>

                            <x-primary-button wire:click="createNew" wire:loading.attr="disabled">
                                Crear cuenta
                            </x-primary-button>
                        </div>
                    </div>
                @endif

                <div class="divide-y divide-gray-100">
                    @forelse ($people as $person)
                        @php
                            $esInstructor = $person->hasRole($instructorRole);
                            $gestionaAcceso = $person->hasPermissionTo($manageAccessPermission);
                            $esUnoMismo = $person->id === auth()->id();
                        @endphp
                        <div class="p-5 flex flex-wrap items-center gap-4">
                            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-brand-50 text-brand-700 font-semibold text-sm shrink-0">
                                {{ strtoupper(substr($person->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900 truncate">{{ $person->name }}</p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ $person->email }}
                                    @if ($person->collaborator)
                                        · {{ $person->collaborator->code }} · {{ $person->collaborator->function }}
                                    @endif
                                    @if ($person->contacto?->cargo)
                                        · {{ $person->contacto->cargo }}
                                    @endif
                                </p>
                                <button wire:click="openEditContact({{ $person->id }})" class="text-xs {{ $person->contacto?->telefono ? 'text-gray-500' : 'text-brand-600' }} hover:text-brand-800 mt-0.5">
                                    {{ $person->contacto?->telefono ? '📱 '.$person->contacto->telefono : '+ Agregar celular' }}
                                </button>

                                @php $otrosRoles = $person->roles->pluck('name')->reject(fn ($r) => $r === $instructorRole); @endphp
                                @if ($otrosRoles->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach ($otrosRoles as $roleName)
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">
                                                {{ $roleName }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-5 rounded-xl border border-gray-100 bg-gray-50/60 px-4 py-2.5 shrink-0">
                                <x-toggle-switch wire:click="askToggleInstructor({{ $person->id }})" :on="$esInstructor">
                                    Instructor
                                </x-toggle-switch>

                                <x-toggle-switch
                                    wire:click="askToggleManageAccess({{ $person->id }})"
                                    :on="$gestionaAcceso"
                                    :disabled="$esUnoMismo && $gestionaAcceso"
                                    title="{{ $esUnoMismo && $gestionaAcceso ? 'No puedes quitarte este permiso a ti mismo' : '' }}"
                                >
                                    Gestión de acceso
                                </x-toggle-switch>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-gray-400">
                            No se encontraron cuentas.
                        </div>
                    @endforelse
                </div>

                @if ($people->hasPages())
                    <div class="p-5 border-t border-gray-100">
                        {{ $people->links() }}
                    </div>
                @endif
            </div>

            {{-- ─── Colaboradores de bodega/CEDI sin cuenta todavía ───────────────── --}}
            @if ($pendingCollaborators->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Sin cuenta todavía</h3>
                        <p class="text-xs text-gray-500 mt-1">Colaboradores de bodega/CEDI que aún no tienen acceso al portal.</p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach ($pendingCollaborators as $collaborator)
                            <div class="p-5 flex flex-wrap items-center gap-4">
                                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-gray-100 text-gray-500 font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($collaborator->first_name, 0, 1).substr($collaborator->last_name, 0, 1)) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-900 truncate">{{ $collaborator->fullName() }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $collaborator->code }} · CI {{ $collaborator->id_card }} · {{ $collaborator->function }}
                                    </p>
                                </div>

                                @if ($activatingId === $collaborator->id)
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                            <input type="checkbox" wire:model="activatingAsInstructor" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                            Es instructor
                                        </label>
                                        <x-primary-button wire:click="confirmActivate" wire:loading.attr="disabled">
                                            Confirmar
                                        </x-primary-button>
                                        <x-secondary-button wire:click="cancelActivate">
                                            Cancelar
                                        </x-secondary-button>
                                    </div>
                                @else
                                    <x-secondary-button wire:click="openActivate({{ $collaborator->id }})">
                                        Activar acceso
                                    </x-secondary-button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── Confirmación antes de asignar/quitar rol o permiso ────────────── --}}
    <x-modal name="confirm-toggle" :show="$confirmingToggle !== null" focusable>
        @if ($confirmingToggle)
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">
                    @if ($confirmingToggle['type'] === 'instructor')
                        {{ $confirmingToggle['willActivate'] ? '¿Asignar rol de instructor?' : '¿Quitar rol de instructor?' }}
                    @else
                        {{ $confirmingToggle['willActivate'] ? '¿Dar permiso de gestión de acceso?' : '¿Quitar permiso de gestión de acceso?' }}
                    @endif
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    @if ($confirmingToggle['type'] === 'instructor')
                        @if ($confirmingToggle['willActivate'])
                            <strong>{{ $confirmingToggle['userName'] }}</strong> va a poder crear módulos y gestionar capacitaciones.
                        @else
                            <strong>{{ $confirmingToggle['userName'] }}</strong> ya no va a poder crear módulos ni gestionar capacitaciones.
                        @endif
                    @else
                        @if ($confirmingToggle['willActivate'])
                            <strong>{{ $confirmingToggle['userName'] }}</strong> va a poder activar cuentas, asignar roles y ver a toda la nómina en esta pantalla.
                        @else
                            <strong>{{ $confirmingToggle['userName'] }}</strong> ya no va a poder gestionar el acceso de los demás.
                        @endif
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

    {{-- ─── Editar celular / cargo de alguien que ya tiene cuenta ─────────── --}}
    <x-modal name="edit-contact" focusable>
        <div class="p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Datos de contacto</h2>

            <div>
                <x-input-label for="editTelefono" value="Celular (WhatsApp)" />
                <x-text-input wire:model="editTelefono" id="editTelefono" class="w-full" placeholder="0991234567" />
                <x-input-error :messages="$errors->get('editTelefono')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="editCargo" value="Cargo (opcional)" />
                <x-text-input wire:model="editCargo" id="editCargo" class="w-full" placeholder="Ej. Analista de RRHH" />
                <x-input-error :messages="$errors->get('editCargo')" class="mt-1" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button wire:click="cancelEditContact">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveContact" wire:loading.attr="disabled">Guardar</x-primary-button>
            </div>
        </div>
    </x-modal>
</div>
