<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Personal y acceso
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ─── Resumen ─────────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalConCuenta }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Con cuenta</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold {{ $totalSinCuenta > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $totalSinCuenta }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Sin cuenta todavía</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalInstructores }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Instructores</p>
                </div>
            </div>

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

            {{-- ─── Barra de acciones ──────────────────────────────────────────── --}}
            <div class="flex items-center justify-between flex-wrap gap-3">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Buscar por nombre o correo..."
                    class="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                >

                <div class="flex items-center gap-2">
                    <a href="{{ route('colaboradores.exportar') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <x-icon-download class="h-4 w-4 text-gray-400" />
                        Exportar
                    </a>
                    <button
                        type="button"
                        wire:click="toggleActivationForm"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg font-semibold text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 {{ $showActivationForm ? 'bg-brand-700 text-white ring-2 ring-brand-500 ring-offset-2' : 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-500' }}"
                    >
                        <x-icon-key class="h-4 w-4" />
                        {{ $showActivationForm ? 'Cerrar' : 'Activar cuentas' }}
                    </button>
                    <button
                        type="button"
                        wire:click="toggleUpdateForm"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg font-semibold text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 {{ $showUpdateForm ? 'bg-gray-700 text-white ring-2 ring-gray-400 ring-offset-2' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-brand-500' }}"
                    >
                        <x-icon-pencil class="h-4 w-4 {{ $showUpdateForm ? '' : 'text-gray-400' }}" />
                        {{ $showUpdateForm ? 'Cerrar' : 'Actualizar datos' }}
                    </button>
                </div>
            </div>

            {{-- ─── Activar cuentas (crea/activa, nunca edita a quien ya tiene) ──── --}}
            @if ($showActivationForm)
                <div class="bg-brand-50/60 rounded-2xl border border-brand-200 shadow-sm p-5 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-full bg-brand-600 text-white shrink-0">
                            <x-icon-key class="h-4 w-4" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-900">Activar cuentas</h3>
                            <p class="text-xs text-gray-600 mt-1 max-w-2xl">
                                Solo para gente <strong>sin cuenta todavía</strong>. Descarga la
                                <a href="{{ route('colaboradores.plantilla-activacion') }}" class="text-brand-700 font-medium hover:underline">plantilla de activación</a>
                                — ya trae a todos con su cédula — completa <code class="text-[11px] bg-white px-1 py-0.5 rounded border border-brand-200">email</code>,
                                <code class="text-[11px] bg-white px-1 py-0.5 rounded border border-brand-200">password</code> y marca
                                <code class="text-[11px] bg-white px-1 py-0.5 rounded border border-brand-200">activo = si</code> solo en quienes quieras activar hoy, y súbela.
                                Se identifica por cédula: si subes el mismo archivo dos veces, no se duplica ni se le cambia la contraseña a quien ya se activó.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-end gap-3 flex-wrap pl-12">
                        <div>
                            <input wire:model="activationFile" type="file" accept=".csv,text/csv" class="text-sm text-gray-600">
                            <x-input-error :messages="$errors->get('activationFile')" class="mt-1" />
                        </div>
                        <x-primary-button wire:click="importActivacion" wire:loading.attr="disabled" wire:target="importActivacion,activationFile">
                            Activar
                        </x-primary-button>
                        <span wire:loading wire:target="activationFile" class="text-xs text-gray-400">Subiendo...</span>
                    </div>

                    @if ($activationSummary)
                        <div class="ml-12 rounded-xl border border-brand-200 bg-white p-4 text-sm space-y-2">
                            <p class="text-gray-700">
                                <strong>{{ $activationSummary['activados'] }}</strong> cuenta(s) activada(s) ·
                                <strong>{{ $activationSummary['omitidos'] }}</strong> omitida(s) (activo=no o ya activada)
                                @if (count($activationSummary['errores']))
                                    · <strong class="text-red-700">{{ count($activationSummary['errores']) }}</strong> error(es)
                                @endif
                            </p>

                            @if (count($activationSummary['errores']))
                                <ul class="list-disc list-inside text-red-700 text-xs space-y-0.5">
                                    @foreach ($activationSummary['errores'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- ─── Actualizar datos (edita, nunca crea ni activa cuentas) ───────── --}}
            @if ($showUpdateForm)
                <div class="bg-gray-50 rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="flex items-center justify-center h-9 w-9 rounded-full bg-gray-700 text-white shrink-0">
                            <x-icon-pencil class="h-4 w-4" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-900">Actualizar datos</h3>
                            <p class="text-xs text-gray-600 mt-1 max-w-2xl">
                                Solo para gente que <strong>ya tiene cuenta</strong>: corrige celular y cargo. Descarga la
                                <a href="{{ route('colaboradores.plantilla-actualizacion') }}" class="text-gray-800 font-medium hover:underline">plantilla de actualización</a>
                                — ya trae los datos actuales de todos —, edítala y súbela. Se identifica por cédula, nunca crea ni activa cuentas nuevas.
                                Nombre y correo son de solo lectura por ahora (la base de datos compartida con XURI APEX no permite editarlos desde acá).
                            </p>
                        </div>
                    </div>

                    <div class="flex items-end gap-3 flex-wrap pl-12">
                        <div>
                            <input wire:model="updateFile" type="file" accept=".csv,text/csv" class="text-sm text-gray-600">
                            <x-input-error :messages="$errors->get('updateFile')" class="mt-1" />
                        </div>
                        <x-primary-button wire:click="importActualizacion" wire:loading.attr="disabled" wire:target="importActualizacion,updateFile">
                            Actualizar
                        </x-primary-button>
                        <span wire:loading wire:target="updateFile" class="text-xs text-gray-400">Subiendo...</span>
                    </div>

                    @if ($updateSummary)
                        <div class="ml-12 rounded-xl border border-gray-200 bg-white p-4 text-sm space-y-2">
                            <p class="text-gray-700">
                                <strong>{{ $updateSummary['actualizados'] }}</strong> contacto(s) actualizado(s)
                                @if (count($updateSummary['errores']))
                                    · <strong class="text-red-700">{{ count($updateSummary['errores']) }}</strong> error(es)
                                @endif
                            </p>

                            @if (count($updateSummary['errores']))
                                <ul class="list-disc list-inside text-red-700 text-xs space-y-0.5">
                                    @foreach ($updateSummary['errores'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- ─── Personal con cuenta ────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <h3 class="font-medium text-gray-900">Personal con cuenta <span class="text-gray-400 font-normal">({{ $totalConCuenta }})</span></h3>
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

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-separate border-spacing-0">
                        <thead>
                            <tr>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Nombre</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Área / cargo</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Celular</th>
                                <th class="px-6 py-3.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Instructor</th>
                                <th class="px-6 py-3.5 text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider bg-gray-100/80 border-b border-gray-200">Gestión de acceso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($people as $person)
                                @php
                                    $esInstructor = $person->hasRole($instructorRole);
                                    $gestionaAcceso = $person->hasPermissionTo($manageAccessPermission);
                                    $esUnoMismo = $person->id === auth()->id();
                                @endphp
                                <tr class="odd:bg-white even:bg-gray-50/60 hover:bg-brand-50/50 transition-colors duration-150">
                                    <td class="px-6 py-4 align-middle border-b border-gray-100">
                                        <p class="font-semibold text-gray-900 truncate">{{ $person->name }}</p>
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ $person->email }}</p>
                                    </td>
                                    <td class="px-6 py-4 align-middle text-gray-600 whitespace-nowrap border-b border-gray-100">
                                        @if ($person->collaborator)
                                            <span class="block text-[11px] text-gray-400">{{ $person->collaborator->code }}</span>
                                        @endif
                                        <span class="{{ $person->contacto->cargo ?? $person->collaborator->function ?? false ? '' : 'text-gray-300' }}">
                                            {{ $person->contacto->cargo ?? $person->collaborator->function ?? 'Sin asignar' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 align-middle whitespace-nowrap border-b border-gray-100">
                                        <button wire:click="openEditContact({{ $person->id }})" class="text-xs font-medium {{ $person->contacto?->telefono ? 'text-gray-600 hover:text-gray-900' : 'text-brand-600 hover:text-brand-800' }} transition-colors">
                                            {{ $person->contacto?->telefono ?: '+ Agregar' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 align-middle border-b border-gray-100">
                                        <div class="flex justify-center">
                                            <x-toggle-switch wire:click="askToggleInstructor({{ $person->id }})" :on="$esInstructor" title="Instructor para {{ $person->name }}" class="p-3 -m-3 rounded-lg hover:bg-white/80" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle border-b border-gray-100">
                                        <div class="flex justify-center">
                                            <x-toggle-switch
                                                wire:click="askToggleManageAccess({{ $person->id }})"
                                                :on="$gestionaAcceso"
                                                :disabled="$esUnoMismo && $gestionaAcceso"
                                                title="{{ $esUnoMismo && $gestionaAcceso ? 'No puedes quitarte este permiso a ti mismo' : 'Gestión de acceso para '.$person->name }}"
                                                class="p-3 -m-3 rounded-lg hover:bg-white/80"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-sm text-gray-400">No se encontraron cuentas.</td>
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

            {{-- ─── Colaboradores de bodega/CEDI sin cuenta todavía ───────────────── --}}
            @if ($pendingCollaborators->isNotEmpty())
                <div class="bg-white rounded-2xl border border-amber-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-amber-100 bg-amber-50/50">
                        <h3 class="font-medium text-gray-900">Sin cuenta todavía <span class="text-amber-600 font-normal">({{ $totalSinCuenta }})</span></h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Colaboradores de bodega/CEDI que aún no tienen acceso al portal. Activa a todos de una vez con
                            <button type="button" wire:click="toggleActivationForm" class="text-brand-600 font-medium hover:underline">Activar cuentas</button>,
                            o uno por uno acá abajo.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border-separate border-spacing-0">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-amber-700 uppercase tracking-wider bg-amber-100/60 border-b border-amber-200">Nombre</th>
                                    <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-amber-700 uppercase tracking-wider bg-amber-100/60 border-b border-amber-200">Código</th>
                                    <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-amber-700 uppercase tracking-wider bg-amber-100/60 border-b border-amber-200">Cédula</th>
                                    <th class="px-6 py-3.5 text-left text-[11px] font-semibold text-amber-700 uppercase tracking-wider bg-amber-100/60 border-b border-amber-200">Función</th>
                                    <th class="px-6 py-3.5 text-right text-[11px] font-semibold text-amber-700 uppercase tracking-wider bg-amber-100/60 border-b border-amber-200">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingCollaborators as $collaborator)
                                    <tr class="odd:bg-white even:bg-amber-50/40 hover:bg-amber-50 transition-colors duration-150">
                                        <td class="px-6 py-4 align-middle border-b border-amber-100">
                                            <p class="font-semibold text-gray-900 truncate">{{ $collaborator->fullName() }}</p>
                                        </td>
                                        <td class="px-6 py-4 align-middle text-gray-500 whitespace-nowrap border-b border-amber-100">{{ $collaborator->code }}</td>
                                        <td class="px-6 py-4 align-middle text-gray-500 whitespace-nowrap border-b border-amber-100">{{ $collaborator->id_card }}</td>
                                        <td class="px-6 py-4 align-middle text-gray-500 border-b border-amber-100">{{ $collaborator->function }}</td>
                                        <td class="px-6 py-4 align-middle text-right border-b border-amber-100">
                                            @if ($activatingId === $collaborator->id)
                                                <div class="flex items-center justify-end gap-3 flex-wrap">
                                                    <label class="inline-flex items-center gap-2 text-xs text-gray-600">
                                                        <input type="checkbox" wire:model="activatingAsInstructor" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                                        Instructor
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
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

    {{-- ─── Editar celular / cargo / cédula de alguien que ya tiene cuenta ─── --}}
    <x-modal name="edit-contact" focusable>
        <div class="p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Datos de contacto</h2>

            <div>
                <x-input-label for="editCedula" value="Cédula" />
                <x-text-input wire:model="editCedula" id="editCedula" class="w-full" placeholder="0102030405" />
                <x-input-error :messages="$errors->get('editCedula')" class="mt-1" />
                <p class="text-xs text-gray-400 mt-1">La usa "Actualizar datos" para identificar a esta persona al reimportar un CSV.</p>
            </div>

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
