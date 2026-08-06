<?php

namespace App\Livewire\Colaboradores;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Roster de solo lectura. La creación/activación de cuentas, roles y
 * teléfono/cédula ahora se maneja directamente en Xuri Apex (esa app es
 * dueña de esos datos: users.phone, users.id_card, collaborators.function) --
 * acá solo se lee lo que ya existe allá, para no duplicar el dato en dos
 * bases. Lo único que sigue siendo 100% de xr-rrhh es el rol de instructor
 * (quién puede crear módulos/gestionar capacitaciones), por eso es lo único
 * que se puede tocar desde esta pantalla.
 */
#[Layout('layouts.app')]
class GestionAcceso extends Component
{
    use WithPagination;

    /**
     * Nombres legibles para los roles de Xuri Apex que se usan como
     * clasificación de área en administración (gente sin fila en
     * `collaborators`, ej. Contabilidad, Auxiliar administrativo -- no
     * abren ningún panel allá, son solo etiqueta de nómina). El rol de
     * instructor no va acá porque ya tiene su propia columna.
     */
    public const ROLE_LABELS = [
        'admin' => 'Administrador (Xuri Apex)',
        'admin_assistant' => 'Auxiliar administrativo',
        'admin_cedi' => 'Administrador de CEDI',
        'accounting' => 'Contabilidad',
        'cedi_staff' => 'Personal CEDI',
        'cleaning_assistant' => 'Auxiliar de limpieza',
        'commercial' => 'Comercial',
        'commercial_china' => 'Comercial China',
        'commercial_mexico' => 'Comercial México',
        'conteo' => 'Conteo',
        'control_calidad' => 'Control de calidad',
        'despacho' => 'Despacho',
        'driver' => 'Conductor',
        'guard' => 'Guardia',
        'imports' => 'Importaciones',
        'inventory' => 'Inventario',
        'logistics' => 'Logística',
        'owner' => 'Propietario (Xuri Apex)',
        'publicist' => 'Publicista',
        'supervisor_comercial' => 'Supervisor comercial',
        'warehouse' => 'Bodega',
    ];

    public string $search = '';

    /** @var array{userId: int, userName: string, willActivate: bool}|null */
    public ?array $confirmingToggle = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
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

    public function render()
    {
        $people = User::query()
            ->with(['collaborator', 'roles'])
            ->when($this->search, fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.colaboradores.gestion-acceso', [
            'people' => $people,
            'instructorRole' => config('xr_rrhh.instructor_role'),
            'totalConCuenta' => User::count(),
            'totalInstructores' => User::role(config('xr_rrhh.instructor_role'))->count(),
        ]);
    }
}
