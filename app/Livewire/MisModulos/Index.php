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
            ->with([
                'lecturas' => fn ($q) => $q->where('user_id', auth()->id()),
                'certificados' => fn ($q) => $q->where('user_id', auth()->id()),
                'evaluacionIntentos' => fn ($q) => $q->where('user_id', auth()->id())->orderByDesc('completed_at'),
            ])
            ->orderBy('categoria')
            ->orderBy('titulo')
            ->get();

        return view('livewire.mis-modulos.index', [
            'modulos' => $modulos,
        ]);
    }
}
