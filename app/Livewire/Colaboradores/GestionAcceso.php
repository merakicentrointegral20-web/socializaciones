<?php

namespace App\Livewire\Colaboradores;

use App\Actions\GrantCollaboratorAccess;
use App\Models\Collaborator;
use App\Models\ColaboradorContacto;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.app')]
class GestionAcceso extends Component
{
    use WithPagination;

    public string $search = '';

    // ─── Activar acceso a un colaborador sin cuenta (bodega/CEDI) ─────────
    public ?int $activatingId = null;

    public bool $activatingAsInstructor = false;

    // ─── Alta de persona nueva (administración o producción) ──────────────
    public bool $showNewForm = false;

    public string $newName = '';

    public string $newArea = 'administracion';

    public string $newCedula = '';

    public string $newTelefono = '';

    public string $newFuncionProduccion = '';

    public string $newCargoAdministracion = '';

    public bool $newAsInstructor = false;

    // ─── Editar celular/cargo de alguien que ya tiene cuenta ───────────────
    public ?int $editingContactId = null;

    public string $editTelefono = '';

    public string $editCargo = '';

    // ─── Confirmación antes de asignar/quitar rol o permiso ───────────────
    /** @var array{type: string, userId: int, userName: string, willActivate: bool}|null */
    public ?array $confirmingToggle = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openActivate(int $collaboratorId): void
    {
        $this->activatingId = $collaboratorId;
        $this->activatingAsInstructor = false;
    }

    public function cancelActivate(): void
    {
        $this->activatingId = null;
        $this->activatingAsInstructor = false;
    }

    public function confirmActivate(GrantCollaboratorAccess $grantAccess): void
    {
        $collaborator = Collaborator::findOrFail($this->activatingId);
        $role = $this->activatingAsInstructor ? config('xr_rrhh.instructor_role') : null;

        try {
            $result = $grantAccess->forCollaborator($collaborator, $role);
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
        $this->reset(['newName', 'newCedula', 'newTelefono', 'newFuncionProduccion', 'newCargoAdministracion', 'newAsInstructor']);
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
        ], [
            'newTelefono.regex' => 'El celular debe tener 10 dígitos (ej. 0991234567).',
        ], [
            'newName' => 'nombre',
            'newCedula' => 'cédula',
            'newFuncionProduccion' => 'función',
        ]);

        $role = $this->newAsInstructor ? config('xr_rrhh.instructor_role') : null;

        try {
            $result = $grantAccess->forNuevoIngreso(
                fullName: $data['newName'],
                area: $data['newArea'],
                cedula: $data['newCedula'] ?: null,
                telefono: $data['newTelefono'] ?: null,
                funcionProduccion: $data['newFuncionProduccion'] ?: null,
                cargoAdministracion: $data['newCargoAdministracion'] ?: null,
                role: $role,
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
        $contacto = ColaboradorContacto::where('user_id', $userId)->first();

        $this->editingContactId = $userId;
        $this->editTelefono = $contacto?->telefono ?? '';
        $this->editCargo = $contacto?->cargo ?? '';
        $this->resetValidation();
        $this->dispatch('open-modal', 'edit-contact');
    }

    public function cancelEditContact(): void
    {
        $this->reset(['editingContactId', 'editTelefono', 'editCargo']);
        $this->resetValidation();
        $this->dispatch('close-modal', 'edit-contact');
    }

    public function saveContact(): void
    {
        $data = $this->validate([
            'editTelefono' => ['nullable', 'regex:/^[0-9]{10}$/'],
            'editCargo' => ['nullable', 'string', 'max:100'],
        ], [
            'editTelefono.regex' => 'El celular debe tener 10 dígitos (ej. 0991234567).',
        ]);

        ColaboradorContacto::updateOrCreate(
            ['user_id' => $this->editingContactId],
            ['telefono' => $data['editTelefono'] ?: null, 'cargo' => $data['editCargo'] ?: null]
        );

        session()->flash('success', 'Datos de contacto actualizados.');
        $this->cancelEditContact();
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
        ]);
    }
}
