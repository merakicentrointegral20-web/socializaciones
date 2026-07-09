<?php

namespace App\Livewire\Modulos;

use App\Models\Capacitacion;
use App\Models\CapacitacionAsistente;
use App\Models\Modulo;
use App\Models\ModuloLectura;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Seguimiento extends Component
{
    use WithFileUploads;

    public Modulo $modulo;

    public const FILTROS = [
        'todos' => 'Todos',
        'falta_leer' => 'Falta leer',
        'falta_induccion' => 'Falta inducción',
        'falta_evaluacion' => 'Falta evaluación',
        'falta_certificado' => 'Falta certificado',
    ];

    public string $filtro = 'todos';

    /** @var int[] */
    public array $seleccionados = [];

    public string $fecha = '';

    public string $modalidad = '';

    public string $lugar = '';

    // ─── Marcar asistencia de una inducción ya agendada ─────────────────────
    public ?int $capacitacionEditando = null;

    /** @var int[] */
    public array $asistieron = [];

    public $actaArchivo = null;

    public function mount(Modulo $modulo): void
    {
        abort_unless($modulo->created_by === auth()->id(), 403);

        $this->modulo = $modulo;
    }

    public function abrirModalInduccion(): void
    {
        $this->resetValidation();

        if (empty($this->seleccionados)) {
            $this->addError('seleccionados', 'Selecciona al menos una persona que ya haya leído el módulo.');

            return;
        }

        $this->dispatch('open-modal', 'agendar-induccion');
    }

    public function cancelarInduccion(): void
    {
        $this->reset(['fecha', 'modalidad', 'lugar']);
        $this->resetValidation();
        $this->dispatch('close-modal', 'agendar-induccion');
    }

    public function agendarInduccion(): void
    {
        if (empty($this->seleccionados)) {
            $this->dispatch('close-modal', 'agendar-induccion');

            return;
        }

        // Solo se puede agendar inducción a quien ya marcó el módulo como
        // leído y todavía no tiene una inducción agendada -- se revalida en
        // servidor por si los checkboxes se manipulan desde el navegador
        // (los deshabilitados en el front no bastan).
        $yaTienenInduccion = CapacitacionAsistente::whereIn('user_id', $this->seleccionados)
            ->whereHas('capacitacion', fn ($q) => $q->where('modulo_id', $this->modulo->id))
            ->pluck('user_id')
            ->all();

        $idsValidos = ModuloLectura::where('modulo_id', $this->modulo->id)
            ->whereIn('user_id', $this->seleccionados)
            ->whereNotNull('leido_at')
            ->pluck('user_id')
            ->reject(fn ($id) => in_array($id, $yaTienenInduccion))
            ->all();

        if (empty($idsValidos)) {
            $this->addError('seleccionados', 'Solo puedes agendar inducción a personas que ya leyeron el módulo y no tienen una ya agendada.');

            return;
        }

        $data = $this->validate([
            'fecha' => ['required', 'date'],
            'modalidad' => ['nullable', 'string', 'max:50'],
            'lugar' => ['nullable', 'string', 'max:150'],
        ], [], [
            'fecha' => 'fecha',
        ]);

        $capacitacion = $this->modulo->capacitaciones()->create([
            'fecha' => $data['fecha'],
            'modalidad' => $data['modalidad'] ?: null,
            'lugar' => $data['lugar'] ?: null,
            'instructor_user_id' => auth()->id(),
            'created_by' => auth()->id(),
        ]);

        foreach ($idsValidos as $userId) {
            $capacitacion->asistentes()->create(['user_id' => $userId]);
        }

        $this->reset(['seleccionados', 'fecha', 'modalidad', 'lugar']);
        $this->dispatch('close-modal', 'agendar-induccion');
        session()->flash('success', 'Inducción agendada para '.count($idsValidos).' persona(s).');
    }

    public function abrirModalAsistencia(int $capacitacionId): void
    {
        $capacitacion = Capacitacion::with('asistentes')
            ->where('modulo_id', $this->modulo->id)
            ->findOrFail($capacitacionId);

        $this->capacitacionEditando = $capacitacion->id;

        // Por defecto se marca a todo el grupo pre-seleccionado como que
        // asistió; el instructor desmarca a quien no llegó. Si ya se había
        // registrado asistencia antes, se parte de lo que quedó guardado.
        $yaRegistrados = $capacitacion->asistentes->where('asistio', true);

        $this->asistieron = $yaRegistrados->isNotEmpty()
            ? $yaRegistrados->pluck('user_id')->all()
            : $capacitacion->asistentes->pluck('user_id')->all();

        $this->dispatch('open-modal', 'marcar-asistencia');
    }

    public function cancelarAsistencia(): void
    {
        $this->reset(['capacitacionEditando', 'asistieron', 'actaArchivo']);
        $this->resetValidation();
        $this->dispatch('close-modal', 'marcar-asistencia');
    }

    public function guardarAsistencia(): void
    {
        $capacitacion = Capacitacion::with('asistentes')
            ->where('modulo_id', $this->modulo->id)
            ->findOrFail($this->capacitacionEditando);

        $this->validate([
            'actaArchivo' => [$capacitacion->acta_path ? 'nullable' : 'required', 'image', 'max:10240'],
        ], [], [
            'actaArchivo' => 'acta',
        ]);

        if ($this->actaArchivo) {
            $path = $this->actaArchivo->store('capacitaciones/'.$capacitacion->id, 'local');
            $capacitacion->update(['acta_path' => $path]);
        }

        foreach ($capacitacion->asistentes as $asistente) {
            $asistio = in_array($asistente->user_id, $this->asistieron);

            $asistente->update([
                'asistio' => $asistio,
                'asistio_at' => $asistio ? now() : null,
            ]);
        }

        $total = count($this->asistieron);

        $this->reset(['capacitacionEditando', 'asistieron', 'actaArchivo']);
        $this->dispatch('close-modal', 'marcar-asistencia');
        session()->flash('success', 'Asistencia registrada para '.$total.' persona(s). Ya pueden rendir la evaluación.');
    }

    public function render()
    {
        $filas = User::query()
            ->orderBy('name')
            ->with([
                'moduloLecturas' => fn ($q) => $q->where('modulo_id', $this->modulo->id),
                'evaluacionIntentos' => fn ($q) => $q->where('modulo_id', $this->modulo->id)->orderByDesc('completed_at'),
                'certificados' => fn ($q) => $q->where('modulo_id', $this->modulo->id),
                'capacitacionAsistencias' => fn ($q) => $q
                    ->whereHas('capacitacion', fn ($q2) => $q2->where('modulo_id', $this->modulo->id))
                    ->with('capacitacion'),
            ])
            ->get()
            ->map(function (User $usuario) {
                $lectura = $usuario->moduloLecturas->first();
                $induccion = $usuario->capacitacionAsistencias->sortByDesc(fn ($a) => $a->capacitacion->fecha)->first();
                $evaluacion = $usuario->evaluacionIntentos->first();
                $certificado = $usuario->certificados->first();

                return (object) [
                    'usuario' => $usuario,
                    'lectura' => $lectura,
                    'induccion' => $induccion,
                    'evaluacion' => $evaluacion,
                    'certificado' => $certificado,
                    'estado' => match (true) {
                        ! $lectura?->leido_at => 'falta_leer',
                        ! $induccion => 'falta_induccion',
                        ! $certificado && ! $evaluacion?->aprobado => 'falta_evaluacion',
                        ! $certificado => 'falta_certificado',
                        default => 'completo',
                    },
                ];
            });

        if ($this->filtro !== 'todos') {
            $filas = $filas->filter(fn ($fila) => $fila->estado === $this->filtro);
        }

        $capacitaciones = $this->modulo->capacitaciones()
            ->with('asistentes.user')
            ->orderByDesc('fecha')
            ->get();

        return view('livewire.modulos.seguimiento', [
            'filas' => $filas,
            'capacitaciones' => $capacitaciones,
        ]);
    }
}
