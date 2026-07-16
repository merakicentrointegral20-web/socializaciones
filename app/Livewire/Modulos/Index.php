<?php

namespace App\Livewire\Modulos;

use App\Models\Categoria;
use App\Models\Modulo;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // ─── Crear módulo ───────────────────────────────────────────────────────
    public bool $showCreateForm = false;

    public string $titulo = '';

    public string $categoria = '';

    public string $contenido = '';

    public int $umbralAprobacion = 7;

    public string $fechaLimite = '';

    public bool $mostrandoNuevaCategoria = false;

    public string $nuevaCategoria = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleCreateForm(): void
    {
        $this->showCreateForm = ! $this->showCreateForm;
        $this->reset(['titulo', 'categoria', 'contenido', 'umbralAprobacion', 'fechaLimite', 'mostrandoNuevaCategoria', 'nuevaCategoria']);
        $this->umbralAprobacion = 7;
        $this->resetValidation();
    }

    public function agregarCategoria(): void
    {
        $data = $this->validate([
            'nuevaCategoria' => ['required', 'string', 'max:100', 'unique:categorias,nombre'],
        ], [], [
            'nuevaCategoria' => 'categoría',
        ]);

        $categoria = Categoria::create(['nombre' => $data['nuevaCategoria']]);

        $this->categoria = $categoria->nombre;
        $this->nuevaCategoria = '';
        $this->mostrandoNuevaCategoria = false;
    }

    public function create(): void
    {
        $data = $this->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'categoria' => ['required', 'string', 'in:'.implode(',', Categoria::pluck('nombre')->all())],
            'contenido' => ['nullable', 'string'],
            'umbralAprobacion' => ['required', 'integer', 'min:1', 'max:10'],
            'fechaLimite' => ['nullable', 'date'],
        ], [], [
            'titulo' => 'título',
            'categoria' => 'categoría',
            'umbralAprobacion' => 'umbral de aprobación',
            'fechaLimite' => 'fecha límite',
        ]);

        $modulo = Modulo::create([
            'titulo' => $data['titulo'],
            'categoria' => $data['categoria'],
            'contenido' => Modulo::limpiarContenido($data['contenido']),
            'umbral_aprobacion' => $data['umbralAprobacion'],
            'fecha_limite' => $data['fechaLimite'] ?: null,
            'activo' => true,
            'created_by' => auth()->id(),
        ]);

        $this->toggleCreateForm();

        $this->redirect(route('modulos.show', $modulo), navigate: true);
    }

    public function render()
    {
        $modulos = Modulo::query()
            ->with('creador')
            ->withCount('preguntas')
            ->when($this->search, fn ($q) => $q
                ->where('titulo', 'like', "%{$this->search}%")
                ->orWhere('categoria', 'like', "%{$this->search}%")
            )
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.modulos.index', [
            'modulos' => $modulos,
            'categorias' => Categoria::orderBy('nombre')->pluck('nombre'),
        ]);
    }
}
