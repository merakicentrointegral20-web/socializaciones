<?php

namespace App\Livewire\Modulos;

use App\Models\Modulo;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Asignacion extends Component
{
    public Modulo $modulo;

    public bool $todos = false;

    /** @var int[] */
    public array $seleccionados = [];

    public string $search = '';

    public function mount(Modulo $modulo): void
    {
        abort_unless(
            $modulo->created_by === auth()->id()
                || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
            403
        );

        $this->modulo = $modulo;
        $this->todos = $modulo->asignado_a_todos;
        $this->seleccionados = $modulo->asignaciones()->pluck('user_id')->all();
    }

    public function guardar(): void
    {
        if ($this->todos) {
            $this->modulo->update(['asignado_a_todos' => true]);
            $this->modulo->asignaciones()->delete();

            session()->flash('success', 'Módulo asignado a todos los colaboradores.');

            return;
        }

        if (empty($this->seleccionados)) {
            $this->addError('seleccionados', 'Selecciona al menos un colaborador, o marca "Todos".');

            return;
        }

        $this->modulo->update(['asignado_a_todos' => false]);
        $this->modulo->asignaciones()->delete();

        foreach ($this->seleccionados as $userId) {
            $this->modulo->asignaciones()->create([
                'user_id' => $userId,
                'assigned_by' => auth()->id(),
            ]);
        }

        session()->flash('success', count($this->seleccionados).' colaborador(es) asignado(s).');
    }

    public function render()
    {
        $colaboradores = User::query()
            ->orderBy('name')
            ->when($this->search, fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->get();

        return view('livewire.modulos.asignacion', [
            'colaboradores' => $colaboradores,
        ]);
    }
}
