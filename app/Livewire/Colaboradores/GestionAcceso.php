<?php

namespace App\Livewire\Colaboradores;

use App\Actions\GrantCollaboratorAccess;
use App\Models\Collaborator;
use App\Models\ColaboradorContacto;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.app')]
class GestionAcceso extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';

    // ─── Activar acceso a un colaborador sin cuenta (bodega/CEDI) ─────────
    public ?int $activatingId = null;

    public bool $activatingAsInstructor = false;

    public string $activatingXuriApexRole = '';

    // ─── Alta de persona nueva (administración o producción) ──────────────
    public bool $showNewForm = false;

    public string $newName = '';

    public string $newArea = 'administracion';

    public string $newCedula = '';

    public string $newTelefono = '';

    public string $newFuncionProduccion = '';

    public string $newCargoAdministracion = '';

    public bool $newAsInstructor = false;

    public string $newXuriApexRole = '';

    // ─── Editar celular/cargo/cédula de alguien que ya tiene cuenta ────────
    public ?int $editingContactId = null;

    public string $editCedula = '';

    public string $editTelefono = '';

    public string $editCargo = '';

    // ─── Confirmación antes de asignar/quitar rol o permiso ───────────────
    /** @var array{type: string, userId: int, userName: string, willActivate: bool}|null */
    public ?array $confirmingToggle = null;

    // ─── Activación masiva por CSV: cedula, email, password, activo ───────
    // Es el ÚNICO camino masivo que crea cuentas -- separado a propósito del
    // de actualización de abajo, para no mezclar "estoy dando de alta" con
    // "estoy corrigiendo un dato" en el mismo archivo.
    public bool $showActivationForm = false;

    public $activationFile = null;

    /** @var array{creados: int, activados: int, omitidos: int, errores: array<int, string>}|null */
    public ?array $activationSummary = null;

    public const REQUIRED_ACTIVATION_COLUMNS = ['cedula', 'email', 'password', 'activo'];

    // ─── Actualización masiva por CSV: solo celular/cargo/cédula ──────────
    // Nunca crea ni activa cuentas -- solo toca colaborador_contactos, que es
    // 100% de xr-rrhh. Nombre/correo/función quedan de solo lectura porque
    // el usuario de BD no tiene permiso de UPDATE sobre esas columnas de
    // xuri-apex (ver database/mysql-app-user.sql).
    public bool $showUpdateForm = false;

    public $updateFile = null;

    /** @var array{actualizados: int, errores: array<int, string>}|null */
    public ?array $updateSummary = null;

    public const REQUIRED_UPDATE_COLUMNS = ['cedula'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openActivate(int $collaboratorId): void
    {
        $this->activatingId = $collaboratorId;
        $this->activatingAsInstructor = false;
        $this->activatingXuriApexRole = '';
    }

    public function cancelActivate(): void
    {
        $this->activatingId = null;
        $this->activatingAsInstructor = false;
        $this->activatingXuriApexRole = '';
    }

    public function confirmActivate(GrantCollaboratorAccess $grantAccess): void
    {
        $collaborator = Collaborator::findOrFail($this->activatingId);
        $roles = array_filter([
            $this->activatingAsInstructor ? config('xr_rrhh.instructor_role') : null,
            $this->activatingXuriApexRole ?: null,
        ]);

        try {
            $result = $grantAccess->forCollaborator($collaborator, $roles);
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->cancelActivate();

            return;
        }

        session()->flash(
            'access-granted',
            "Acceso activado para {$collaborator->fullName()} · {$result['email']} · Contraseña temporal: {$result['password']}"
        );

        $this->cancelActivate();
    }

    public function toggleNewForm(): void
    {
        $this->showNewForm = ! $this->showNewForm;
        $this->reset(['newName', 'newCedula', 'newTelefono', 'newFuncionProduccion', 'newCargoAdministracion', 'newAsInstructor', 'newXuriApexRole']);
        $this->newArea = 'administracion';
        $this->resetValidation();
    }

    public function createNew(GrantCollaboratorAccess $grantAccess): void
    {
        $data = $this->validate([
            'newName' => ['required', 'string', 'min:3', 'max:150'],
            'newArea' => ['required', 'in:administracion,produccion'],
            'newCedula' => [$this->newArea === 'produccion' ? 'required' : 'nullable', 'string', 'max:20'],
            'newTelefono' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'newFuncionProduccion' => [$this->newArea === 'produccion' ? 'required' : 'nullable', 'in:'.implode(',', GrantCollaboratorAccess::FUNCIONES_PRODUCCION)],
            'newCargoAdministracion' => [$this->newArea === 'administracion' ? 'nullable' : 'nullable', 'string', 'max:100'],
            'newXuriApexRole' => ['nullable', 'in:'.implode(',', GrantCollaboratorAccess::xuriApexRoles())],
        ], [
            'newTelefono.regex' => 'El celular debe tener 10 dígitos (ej. 0991234567).',
        ], [
            'newName' => 'nombre',
            'newCedula' => 'cédula',
            'newFuncionProduccion' => 'función',
            'newXuriApexRole' => 'rol en Xuri Apex',
        ]);

        $roles = array_filter([
            $this->newAsInstructor ? config('xr_rrhh.instructor_role') : null,
            $data['newXuriApexRole'] ?: null,
        ]);

        try {
            $result = $grantAccess->forNuevoIngreso(
                fullName: $data['newName'],
                area: $data['newArea'],
                cedula: $data['newCedula'] ?: null,
                telefono: $data['newTelefono'] ?: null,
                funcionProduccion: $data['newFuncionProduccion'] ?: null,
                cargoAdministracion: $data['newCargoAdministracion'] ?: null,
                roles: $roles,
            );
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $reflejo = $data['newArea'] === 'produccion' ? ' · reflejado en XURI APEX' : '';

        session()->flash(
            'access-granted',
            "Cuenta creada para {$data['newName']}{$reflejo} · {$result['email']} · Contraseña temporal: {$result['password']}"
        );

        $this->toggleNewForm();
    }

    public function openEditContact(int $userId): void
    {
        $user = User::with('collaborator')->findOrFail($userId);
        $contacto = ColaboradorContacto::where('user_id', $userId)->first();

        $this->editingContactId = $userId;
        $this->editCedula = $contacto?->cedula ?? $user->collaborator?->id_card ?? '';
        $this->editTelefono = $contacto?->telefono ?? '';
        $this->editCargo = $contacto?->cargo ?? '';
        $this->resetValidation();
        $this->dispatch('open-modal', 'edit-contact');
    }

    public function cancelEditContact(): void
    {
        $this->reset(['editingContactId', 'editCedula', 'editTelefono', 'editCargo']);
        $this->resetValidation();
        $this->dispatch('close-modal', 'edit-contact');
    }

    public function saveContact(): void
    {
        $data = $this->validate([
            'editCedula' => ['nullable', 'string', 'max:20', 'unique:colaborador_contactos,cedula,'.$this->editingContactId.',user_id'],
            'editTelefono' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'editCargo' => ['nullable', 'string', 'max:100'],
        ], [
            'editTelefono.regex' => 'El celular debe tener 10 dígitos (ej. 0991234567).',
            'editCedula.unique' => 'Esa cédula ya está registrada en otra cuenta.',
        ], [
            'editCedula' => 'cédula',
        ]);

        ColaboradorContacto::updateOrCreate(
            ['user_id' => $this->editingContactId],
            ['cedula' => $data['editCedula'] ?: null, 'telefono' => $data['editTelefono'] ?: null, 'cargo' => $data['editCargo'] ?: null]
        );

        session()->flash('success', 'Datos de contacto actualizados.');
        $this->cancelEditContact();
    }

    public function toggleActivationForm(): void
    {
        $this->showActivationForm = ! $this->showActivationForm;
        $this->reset(['activationFile', 'activationSummary']);
        $this->resetValidation();
    }

    /**
     * Importa desde colaboradores.plantilla-activacion. Es el ÚNICO camino
     * masivo que crea/activa cuentas. Por fila, en este orden:
     *  - activo != "si": se omite sin error -- así se puede tener a todo el
     *    mundo en el archivo y procesar solo a quien corresponda hoy.
     *  - la cédula coincide con alguien de "Sin cuenta todavía": se activa
     *    con el email/password EXACTOS del archivo (no se autogeneran) vía
     *    GrantCollaboratorAccess::forCollaborator, igual que el botón
     *    "Activar acceso".
     *  - la cédula ya tiene cuenta (activada en una corrida anterior): se
     *    omite sin error -- resubir el mismo archivo no la duplica ni le
     *    cambia el email/password.
     *  - la cédula no coincide con NADIE (ni pendiente ni ya activada):
     *    persona totalmente nueva -- se crea desde cero vía
     *    GrantCollaboratorAccess::forNuevoIngreso, igual que
     *    "+ Agregar colaborador" pero en lote. Área por defecto
     *    "administracion" si la columna viene vacía (el caso típico: gente
     *    que Talento Humano necesita en el sistema pero que todavía no
     *    participa en Xuri Apex).
     */
    public function importActivacion(GrantCollaboratorAccess $grantAccess): void
    {
        $this->validate([
            'activationFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [], ['activationFile' => 'archivo']);

        $handle = fopen($this->activationFile->getRealPath(), 'r');
        $header = fgetcsv($handle) ?: [];
        $header[0] = isset($header[0]) ? preg_replace('/^\xEF\xBB\xBF/', '', $header[0]) : '';
        $header = array_map('trim', $header);

        $faltantes = array_diff(self::REQUIRED_ACTIVATION_COLUMNS, $header);

        if ($faltantes) {
            fclose($handle);
            session()->flash('error', 'Al archivo de activación le faltan estas columnas: '.implode(', ', $faltantes).'. Descarga primero la plantilla de activación.');

            return;
        }

        $creados = 0;
        $activados = 0;
        $omitidos = 0;
        $errores = [];
        $numeroFila = 1;
        $numColumnas = count($header);

        while (($row = fgetcsv($handle)) !== false) {
            $numeroFila++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = array_slice(array_pad($row, $numColumnas, ''), 0, $numColumnas);
            $data = array_map('trim', array_combine($header, $row));

            if (! in_array(strtolower($data['activo']), ['si', 'sí', '1', 'true', 'x'], true)) {
                $omitidos++;

                continue;
            }

            $cedula = $data['cedula'] ?: null;
            $email = $data['email'] !== '' ? strtolower($data['email']) : null;
            $password = $data['password'] !== '' ? $data['password'] : null;

            try {
                if (! $cedula) {
                    throw new RuntimeException('falta la cédula.');
                }

                if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new RuntimeException('el email no es válido.');
                }

                if (! $password || strlen($password) < 8) {
                    throw new RuntimeException('la contraseña debe tener al menos 8 caracteres.');
                }

                $rolXuriApex = trim($data['rol_xuri_apex'] ?? '');

                if ($rolXuriApex !== '' && ! in_array($rolXuriApex, GrantCollaboratorAccess::xuriApexRoles(), true)) {
                    throw new RuntimeException("\"{$rolXuriApex}\" no es un rol válido de Xuri Apex.");
                }

                $roles = array_filter([$rolXuriApex ?: null]);
                $pendiente = Collaborator::whereNull('user_id')->where('id_card', $cedula)->first();

                if ($pendiente) {
                    $result = $grantAccess->forCollaborator($pendiente, $roles, $email, $password);

                    ColaboradorContacto::updateOrCreate(
                        ['user_id' => $result['user']->id],
                        ['cedula' => $cedula]
                    );
                    $activados++;

                    continue;
                }

                $yaActivada = Collaborator::where('id_card', $cedula)->whereNotNull('user_id')->exists()
                    || ColaboradorContacto::where('cedula', $cedula)->exists();

                if ($yaActivada) {
                    $omitidos++;

                    continue;
                }

                if ($data['nombre'] === '') {
                    throw new RuntimeException('falta el nombre (la cédula no coincide con nadie existente, así que hace falta el nombre para crearla).');
                }

                $area = ($data['area'] ?? '') === 'produccion' ? 'produccion' : 'administracion';

                $grantAccess->forNuevoIngreso(
                    fullName: $data['nombre'],
                    area: $area,
                    cedula: $cedula,
                    telefono: ($data['telefono'] ?? '') ?: null,
                    funcionProduccion: $area === 'produccion' ? ($data['funcion_cargo'] ?? null) : null,
                    cargoAdministracion: $area === 'administracion' ? (($data['funcion_cargo'] ?? '') ?: null) : null,
                    roles: $roles,
                    email: $email,
                    password: $password,
                );
                $creados++;
            } catch (\Throwable $e) {
                $errores[] = "Fila {$numeroFila}: {$e->getMessage()}";
            }
        }

        fclose($handle);

        $this->activationSummary = compact('creados', 'activados', 'omitidos', 'errores');
        $this->reset(['activationFile']);
    }

    public function toggleUpdateForm(): void
    {
        $this->showUpdateForm = ! $this->showUpdateForm;
        $this->reset(['updateFile', 'updateSummary']);
        $this->resetValidation();
    }

    /**
     * Importa desde colaboradores.plantilla-actualizacion (columnas: cedula,
     * telefono, cargo -- nombre viaja de referencia y se ignora). Nunca crea
     * ni activa cuentas -- solo actualiza colaborador_contactos de alguien
     * que YA tiene cuenta, encontrado por cédula (única). Nombre/correo/
     * función no se pueden editar: el usuario de BD no tiene permiso de
     * UPDATE sobre esas columnas de xuri-apex (ver database/mysql-app-user.sql).
     */
    public function importActualizacion(): void
    {
        $this->validate([
            'updateFile' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [], ['updateFile' => 'archivo']);

        $handle = fopen($this->updateFile->getRealPath(), 'r');
        $header = fgetcsv($handle) ?: [];
        $header[0] = isset($header[0]) ? preg_replace('/^\xEF\xBB\xBF/', '', $header[0]) : '';
        $header = array_map('trim', $header);

        $faltantes = array_diff(self::REQUIRED_UPDATE_COLUMNS, $header);

        if ($faltantes) {
            fclose($handle);
            session()->flash('error', 'Al archivo de actualización le faltan estas columnas: '.implode(', ', $faltantes).'. Descarga primero la plantilla de actualización.');

            return;
        }

        $actualizados = 0;
        $errores = [];
        $numeroFila = 1;
        $numColumnas = count($header);

        while (($row = fgetcsv($handle)) !== false) {
            $numeroFila++;

            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = array_slice(array_pad($row, $numColumnas, ''), 0, $numColumnas);
            $data = array_map('trim', array_combine($header, $row));
            $cedula = $data['cedula'] ?: null;

            try {
                if (! $cedula) {
                    throw new RuntimeException('falta la cédula.');
                }

                $existente = Collaborator::where('id_card', $cedula)->whereNotNull('user_id')->first()?->user
                    ?? ColaboradorContacto::where('cedula', $cedula)->first()?->user;

                if (! $existente) {
                    throw new RuntimeException("la cédula {$cedula} no coincide con nadie que ya tenga cuenta. Para crear/activar cuentas usa el archivo de activación.");
                }

                ColaboradorContacto::updateOrCreate(
                    ['user_id' => $existente->id],
                    [
                        'cedula' => $cedula,
                        'telefono' => ($data['telefono'] ?? '') !== '' ? $data['telefono'] : null,
                        'cargo' => ($data['cargo'] ?? '') !== '' ? $data['cargo'] : null,
                    ]
                );
                $actualizados++;
            } catch (\Throwable $e) {
                $errores[] = "Fila {$numeroFila}: {$e->getMessage()}";
            }
        }

        fclose($handle);

        $this->updateSummary = compact('actualizados', 'errores');
        $this->reset(['updateFile']);
    }

    public function askToggleInstructor(int $userId): void
    {
        $user = User::findOrFail($userId);
        $role = config('xr_rrhh.instructor_role');

        $this->confirmingToggle = [
            'type' => 'instructor',
            'userId' => $user->id,
            'userName' => $user->name,
            'willActivate' => ! $user->hasRole($role),
        ];
        $this->dispatch('open-modal', 'confirm-toggle');
    }

    public function askToggleManageAccess(int $userId): void
    {
        $user = User::findOrFail($userId);
        $permission = config('xr_rrhh.manage_access_permission');

        $this->confirmingToggle = [
            'type' => 'manage_access',
            'userId' => $user->id,
            'userName' => $user->name,
            'willActivate' => ! $user->hasPermissionTo($permission),
        ];
        $this->dispatch('open-modal', 'confirm-toggle');
    }

    public function cancelToggle(): void
    {
        $this->confirmingToggle = null;
        $this->dispatch('close-modal', 'confirm-toggle');
    }

    public function applyToggle(): void
    {
        if (! $this->confirmingToggle) {
            return;
        }

        ['type' => $type, 'userId' => $userId, 'userName' => $name, 'willActivate' => $willActivate] = $this->confirmingToggle;

        if ($type === 'manage_access' && ! $willActivate && $userId === auth()->id()) {
            session()->flash('error', 'No puedes quitarte a ti mismo el permiso de gestión de acceso. Pídele a otra persona con ese permiso que lo haga.');
            $this->confirmingToggle = null;
            $this->dispatch('close-modal', 'confirm-toggle');

            return;
        }

        $user = User::findOrFail($userId);

        if ($type === 'instructor') {
            $role = config('xr_rrhh.instructor_role');
            $willActivate ? $user->assignRole($role) : $user->removeRole($role);
            session()->flash('success', $willActivate ? "{$name} ahora es instructor." : "{$name} ya no es instructor.");
        } else {
            $permission = config('xr_rrhh.manage_access_permission');
            $willActivate ? $user->givePermissionTo($permission) : $user->revokePermissionTo($permission);
            session()->flash('success', $willActivate ? "{$name} ahora puede gestionar acceso." : "{$name} ya no puede gestionar acceso.");
        }

        $this->confirmingToggle = null;
        $this->dispatch('close-modal', 'confirm-toggle');
    }

    public function render()
    {
        $people = User::query()
            ->with(['collaborator', 'roles', 'contacto'])
            ->when($this->search, fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(15);

        $pendingCollaborators = Collaborator::query()
            ->whereNull('user_id')
            ->when($this->search, fn ($q) => $q
                ->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")
                ->orWhere('id_card', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")
            )
            ->orderBy('last_name')
            ->get();

        return view('livewire.colaboradores.gestion-acceso', [
            'people' => $people,
            'pendingCollaborators' => $pendingCollaborators,
            'instructorRole' => config('xr_rrhh.instructor_role'),
            'manageAccessPermission' => config('xr_rrhh.manage_access_permission'),
            'funcionesProduccion' => GrantCollaboratorAccess::FUNCIONES_PRODUCCION,
            'xuriApexRoles' => GrantCollaboratorAccess::xuriApexRoles(),
            'xuriApexRoleLabels' => GrantCollaboratorAccess::XURI_APEX_ROLE_LABELS,
            // Totales para las tarjetas de arriba -- sin el filtro de búsqueda,
            // son el conteo real del sistema, no de la página actual.
            'totalConCuenta' => User::count(),
            'totalSinCuenta' => Collaborator::whereNull('user_id')->count(),
            'totalInstructores' => User::role(config('xr_rrhh.instructor_role'))->count(),
        ]);
    }
}
