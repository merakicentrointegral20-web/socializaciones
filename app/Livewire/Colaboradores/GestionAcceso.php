<?php

namespace App\Livewire\Colaboradores;

use App\Models\Collaborator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class GestionAcceso extends Component
{
    use WithPagination;

    public string $search = '';

    // ─── Modal Crear Colaborador ─────────────────────────────────────────────
    public bool $mostrandoModalCrear = false;

    public string $nuevoNombre = '';

    public string $nuevoEmail = '';

    public string $nuevaCedula = '';

    public string $nuevoTelefono = '';

    public string $nuevoCargo = '';

    public string $nuevoPassword = '';

    public bool $esInstructor = false;

    public bool $esSupervision = false;

    // ─── Modal Confirmar Toggle Rol ─────────────────────────────────────────
    /** @var array{userId: int, userName: string, willActivate: bool}|null */
    public ?array $confirmingToggle = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function abrirModalCrear(): void
    {
        $this->reset([
            'nuevoNombre',
            'nuevoEmail',
            'nuevaCedula',
            'nuevoTelefono',
            'nuevoCargo',
            'nuevoPassword',
            'esInstructor',
            'esSupervision',
        ]);
        $this->resetValidation();
        $this->mostrandoModalCrear = true;
        $this->dispatch('open-modal', 'modal-crear-colaborador');
    }

    public function cerrarModalCrear(): void
    {
        $this->mostrandoModalCrear = false;
        $this->dispatch('close-modal', 'modal-crear-colaborador');
    }

    public function guardarColaborador(): void
    {
        $data = $this->validate([
            'nuevoNombre' => ['required', 'string', 'max:150'],
            'nuevoEmail' => ['required', 'email', 'max:150', 'unique:users,email'],
            'nuevaCedula' => ['nullable', 'string', 'max:25'],
            'nuevoTelefono' => ['nullable', 'string', 'max:30'],
            'nuevoCargo' => ['nullable', 'string', 'max:100'],
            'nuevoPassword' => ['nullable', 'string', 'min:6'],
        ], [], [
            'nuevoNombre' => 'nombre',
            'nuevoEmail' => 'correo electrónico',
            'nuevaCedula' => 'cédula',
            'nuevoTelefono' => 'celular',
            'nuevoCargo' => 'cargo / área',
            'nuevoPassword' => 'contraseña',
        ]);

        $passwordPlano = $data['nuevoPassword'] ?: 'password';

        // 1. Crear el usuario
        $user = User::create([
            'name' => $data['nuevoNombre'],
            'email' => strtolower(trim($data['nuevoEmail'])),
            'password' => Hash::make($passwordPlano),
            'id_card' => $data['nuevaCedula'] ?: null,
            'phone' => $data['nuevoTelefono'] ?: null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Crear el colaborador asociado
        $partes = explode(' ', trim($data['nuevoNombre']), 2);
        Collaborator::create([
            'user_id' => $user->id,
            'first_name' => $partes[0] ?? $data['nuevoNombre'],
            'last_name' => $partes[1] ?? '',
            'id_card' => $data['nuevaCedula'] ?: null,
            'function' => $data['nuevoCargo'] ?: null,
            'email' => strtolower(trim($data['nuevoEmail'])),
        ]);

        // 3. Asignar roles si corresponde
        if ($this->esInstructor) {
            $user->assignRole(config('xr_rrhh.instructor_role'));
        }

        if ($this->esSupervision) {
            $rolSupervision = Role::findOrCreate(config('xr_rrhh.supervision_role'), 'web');
            $user->assignRole($rolSupervision);
        }

        $this->cerrarModalCrear();
        session()->flash('success', "Colaborador {$user->name} creado exitosamente. Contraseña asignada: {$passwordPlano}");
    }

    public function toggleEstadoCuenta(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->is_active = ! $user->is_active;
        $user->save();

        session()->flash('success', $user->is_active ? "Cuenta de {$user->name} activada." : "Cuenta de {$user->name} desactivada.");
    }

    public function askToggleInstructor(int $userId): void
    {
        $user = User::findOrFail($userId);
        $role = config('xr_rrhh.instructor_role');

        $this->confirmingToggle = [
            'userId' => $user->id,
            'userName' => $user->name,
            'willActivate' => ! $user->hasRole($role),
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

        ['userId' => $userId, 'userName' => $name, 'willActivate' => $willActivate] = $this->confirmingToggle;

        $user = User::findOrFail($userId);
        $role = config('xr_rrhh.instructor_role');

        $willActivate ? $user->assignRole($role) : $user->removeRole($role);
        session()->flash('success', $willActivate ? "{$name} ahora es instructor." : "{$name} ya no es instructor.");

        $this->confirmingToggle = null;
        $this->dispatch('close-modal', 'confirm-toggle');
    }

    public function toggleSupervision(int $userId): void
    {
        $user = User::findOrFail($userId);
        $role = Role::findOrCreate(config('xr_rrhh.supervision_role'), 'web');

        if ($user->hasRole($role)) {
            $user->removeRole($role);
            session()->flash('success', "{$user->name} vuelve a aparecer en actas y seguimiento.");
        } else {
            $user->assignRole($role);
            session()->flash('success', "{$user->name} quedó como solo supervisión: ya no sale en actas ni en seguimiento.");
        }
    }

    public function render()
    {
        $people = User::query()
            ->with(['collaborator', 'roles'])
            ->when($this->search, fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('id_card', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.colaboradores.gestion-acceso', [
            'people' => $people,
            'instructorRole' => config('xr_rrhh.instructor_role'),
            'supervisionRole' => config('xr_rrhh.supervision_role'),
            'totalConCuenta' => User::count(),
            'totalInstructores' => User::role(config('xr_rrhh.instructor_role'))->count(),
        ]);
    }
}
