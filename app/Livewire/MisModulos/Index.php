<?php

namespace App\Livewire\MisModulos;

use App\Models\Modulo;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        $modulos = Modulo::query()
            ->where('activo', true)
            ->where(fn ($q) => $q->where('asignado_a_todos', true)
                ->orWhereHas('asignaciones', fn ($q2) => $q2->where('user_id', auth()->id())))
            ->with([
                'lecturas' => fn ($q) => $q->where('user_id', auth()->id()),
                'certificados' => fn ($q) => $q->where('user_id', auth()->id()),
                'evaluacionIntentos' => fn ($q) => $q->where('user_id', auth()->id())->orderByDesc('completed_at')->orderByDesc('id'),
            ])
            ->orderBy('categoria')
            ->orderBy('titulo')
            ->get();

        return view('livewire.mis-modulos.index', [
            'modulos' => $modulos,
        ]);
    }
}
