<?php

namespace App\Livewire;

use App\Models\Certificado;
use App\Models\Modulo;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function mount(): void
    {
        if (! auth()->user()->can('modulos.crear')) {
            $this->redirect(route('mis-modulos.index'));
        }
    }

    public function render()
    {
        $modulos = Modulo::where('created_by', auth()->id())
            ->where('activo', true)
            ->orderBy('titulo')
            ->get();

        $totalNomina = User::participantes()->count();

        $certificadosPorModulo = Certificado::whereIn('modulo_id', $modulos->pluck('id'))
            ->get()
            ->groupBy('modulo_id');

        $resumenModulos = $modulos->map(function (Modulo $modulo) use ($certificadosPorModulo, $totalNomina) {
            $completados = $certificadosPorModulo->get($modulo->id, collect())->count();
            $totalObjetivo = $modulo->asignado_a_todos
                ? $totalNomina
                : $modulo->asignaciones()->count();

            return (object) [
                'modulo' => $modulo,
                'completados' => $completados,
                'pendientes' => max($totalObjetivo - $completados, 0),
                'porcentaje' => $totalObjetivo > 0 ? (int) round($completados / $totalObjetivo * 100) : 0,
            ];
        });

        $certificadosPorUsuario = Certificado::whereIn('modulo_id', $modulos->pluck('id'))
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('modulo_id')->all());

        $personasConPendientes = User::participantes()->orderBy('name')
            ->get()
            ->map(function (User $user) use ($modulos, $certificadosPorUsuario) {
                $completados = $certificadosPorUsuario->get($user->id, []);
                $modulosAsignados = $modulos->filter(fn (Modulo $m) => $m->visiblePara($user));

                return (object) [
                    'user' => $user,
                    'pendientes' => $modulosAsignados->reject(fn (Modulo $m) => in_array($m->id, $completados)),
                ];
            })
            ->filter(fn ($fila) => $fila->pendientes->isNotEmpty())
            ->values();

        return view('livewire.dashboard', [
            'totalNomina' => $totalNomina,
            'resumenModulos' => $resumenModulos,
            'personasConPendientes' => $personasConPendientes,
        ]);
    }
}
