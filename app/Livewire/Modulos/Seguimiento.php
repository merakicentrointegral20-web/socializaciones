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

    // ─── Reagendar una inducción ya agendada (aún sin asistencia marcada) ───
    public ?int $capacitacionReagendando = null;

    public string $fechaReagendar = '';

    public string $modalidadReagendar = '';

    public string $lugarReagendar = '';

    /** @var int[] */
    public array $seleccionadosParaAgregar = [];

    public function mount(Modulo $modulo): void
    {
        abort_unless(
            $modulo->created_by === auth()->id()
                || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
            403
        );

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

        if ($this->hayConflictoDeHorario($data['fecha'])) {
            $this->addError('fecha', 'Ya tienes otra inducción agendada a menos de 30 minutos de esa hora.');

            return;
        }

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

    // Evita agendar dos inducciones casi encimadas por error (ej. doble
    // clic o crear otra sin revisar que ya existía una a esa hora) -- se
    // compara contra TODAS las inducciones del mismo instructor, sin
    // importar el módulo, porque físicamente no puede dar dos a la vez.
    // $excluirCapacitacionId se usa al reagendar, para no chocar contra sí
    // misma.
    private function hayConflictoDeHorario(string $fecha, ?int $excluirCapacitacionId = null): bool
    {
        $momento = \Illuminate\Support\Carbon::parse($fecha);

        return Capacitacion::where('instructor_user_id', auth()->id())
            ->when($excluirCapacitacionId, fn ($q) => $q->whereKeyNot($excluirCapacitacionId))
            ->whereBetween('fecha', [$momento->copy()->subMinutes(30), $momento->copy()->addMinutes(30)])
            ->exists();
    }

    public function abrirModalAsistencia(int $capacitacionId): void
    {
        $capacitacion = Capacitacion::with('asistentes')
            ->where('modulo_id', $this->modulo->id)
            ->findOrFail($capacitacionId);

        // El botón ya está oculto en la vista hasta que pase la fecha, pero
        // se revalida en servidor por si el método se invoca directo (misma
        // lógica que ya se aplica al agendar/reagendar).
        if ($capacitacion->fecha->isFuture()) {
            session()->flash('error', 'Todavía no puedes marcar asistencia: espera a que pase la fecha de la inducción.');

            return;
        }

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

        abort_unless($capacitacion->fecha->isPast(), 403);

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

    public function abrirModalReagendar(int $capacitacionId): void
    {
        $capacitacion = Capacitacion::where('modulo_id', $this->modulo->id)
            ->whereNull('acta_path')
            ->findOrFail($capacitacionId);

        $this->resetValidation();
        $this->capacitacionReagendando = $capacitacion->id;
        $this->fechaReagendar = $capacitacion->fecha->format('Y-m-d\TH:i');
        $this->modalidadReagendar = (string) $capacitacion->modalidad;
        $this->lugarReagendar = (string) $capacitacion->lugar;
        $this->seleccionadosParaAgregar = [];

        $this->dispatch('open-modal', 'reagendar-induccion');
    }

    public function cancelarReagendar(): void
    {
        $this->reset(['capacitacionReagendando', 'fechaReagendar', 'modalidadReagendar', 'lugarReagendar', 'seleccionadosParaAgregar']);
        $this->resetValidation();
        $this->dispatch('close-modal', 'reagendar-induccion');
    }

    public function guardarReagendamiento(): void
    {
        // Revalida que siga sin asistencia marcada -- si alguien ya subió el
        // acta mientras el modal estaba abierto, no tiene sentido cambiar la
        // fecha de una sesión que ya se dio por concluida.
        $capacitacion = Capacitacion::where('modulo_id', $this->modulo->id)
            ->whereNull('acta_path')
            ->findOrFail($this->capacitacionReagendando);

        $data = $this->validate([
            'fechaReagendar' => ['required', 'date'],
            'modalidadReagendar' => ['nullable', 'string', 'max:50'],
            'lugarReagendar' => ['nullable', 'string', 'max:150'],
        ], [], [
            'fechaReagendar' => 'fecha',
        ]);

        if ($this->hayConflictoDeHorario($data['fechaReagendar'], excluirCapacitacionId: $capacitacion->id)) {
            $this->addError('fechaReagendar', 'Ya tienes otra inducción agendada a menos de 30 minutos de esa hora.');

            return;
        }

        $capacitacion->update([
            'fecha' => $data['fechaReagendar'],
            'modalidad' => $data['modalidadReagendar'] ?: null,
            'lugar' => $data['lugarReagendar'] ?: null,
        ]);

        // Agregar colaboradores olvidados, sin tocar a los que ya estaban
        // (sus filas de asistencia existentes -- si las hubiera -- se
        // mantienen intactas). Misma revalidación que agendarInduccion: solo
        // quien ya leyó el módulo y no tiene inducción en ninguna otra
        // sesión de este módulo.
        if (! empty($this->seleccionadosParaAgregar)) {
            $yaTienenInduccion = CapacitacionAsistente::whereIn('user_id', $this->seleccionadosParaAgregar)
                ->whereHas('capacitacion', fn ($q) => $q->where('modulo_id', $this->modulo->id))
                ->pluck('user_id')
                ->all();

            $idsValidos = ModuloLectura::where('modulo_id', $this->modulo->id)
                ->whereIn('user_id', $this->seleccionadosParaAgregar)
                ->whereNotNull('leido_at')
                ->pluck('user_id')
                ->reject(fn ($id) => in_array($id, $yaTienenInduccion))
                ->all();

            foreach ($idsValidos as $userId) {
                $capacitacion->asistentes()->create(['user_id' => $userId]);
            }
        }

        $this->reset(['capacitacionReagendando', 'fechaReagendar', 'modalidadReagendar', 'lugarReagendar', 'seleccionadosParaAgregar']);
        $this->dispatch('close-modal', 'reagendar-induccion');
        session()->flash('success', 'Inducción reagendada.');
    }

    public function eliminarInduccion(): void
    {
        // Mismo candado que reagendar: si ya se subió el acta, la sesión ya
        // se dio por concluida y no se puede borrar desde acá. El borrado
        // se lleva de paso, por la cascada de la FK, a sus filas de
        // capacitacion_asistentes -- así quienes estaban invitados quedan
        // libres para que se les agende una inducción nueva.
        $capacitacion = Capacitacion::where('modulo_id', $this->modulo->id)
            ->whereNull('acta_path')
            ->findOrFail($this->capacitacionReagendando);

        $capacitacion->delete();

        $this->reset(['capacitacionReagendando', 'fechaReagendar', 'modalidadReagendar', 'lugarReagendar', 'seleccionadosParaAgregar']);
        $this->dispatch('close-modal', 'reagendar-induccion');
        session()->flash('success', 'Inducción eliminada.');
    }

    public function render()
    {
        $filas = User::query()
            ->orderBy('name')
            ->with([
                'moduloLecturas' => fn ($q) => $q->where('modulo_id', $this->modulo->id),
                'evaluacionIntentos' => fn ($q) => $q->where('modulo_id', $this->modulo->id)->orderByDesc('completed_at')->orderByDesc('id'),
                'certificados' => fn ($q) => $q->where('modulo_id', $this->modulo->id),
                'capacitacionAsistencias' => fn ($q) => $q
                    ->whereHas('capacitacion', fn ($q2) => $q2->where('modulo_id', $this->modulo->id))
                    ->with('capacitacion'),
            ])
            ->get()
            ->map(function (User $usuario) {
                $lectura = $usuario->moduloLecturas->first();
                $induccion = $usuario->capacitacionAsistencias->sortByDesc(fn ($a) => $a->capacitacion->fecha)->first();
                $certificado = $usuario->certificados->first();

                // Si ya tiene certificado, se muestra el intento que lo generó
                // (el que aprobó), no el último intento registrado -- podría
                // haber uno posterior reprobado (ej. retomó el módulo) que de
                // otro modo lo dejaría viéndose como "reprobado" aunque ya
                // esté certificado.
                $evaluacion = $certificado
                    ? $usuario->evaluacionIntentos->firstWhere('id', $certificado->evaluacion_intento_id)
                    : null;
                $evaluacion ??= $usuario->evaluacionIntentos->first();

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

        // Para el modal de reagendar: a quién se puede agregar todavía --
        // mismo criterio que habilita el checkbox en la tabla principal
        // (leyó el módulo, sin inducción agendada en ninguna sesión de él).
        $candidatosParaAgregar = User::query()
            ->orderBy('name')
            ->whereHas('moduloLecturas', fn ($q) => $q
                ->where('modulo_id', $this->modulo->id)
                ->whereNotNull('leido_at'))
            ->whereDoesntHave('capacitacionAsistencias', fn ($q) => $q
                ->whereHas('capacitacion', fn ($q2) => $q2->where('modulo_id', $this->modulo->id)))
            ->get();

        return view('livewire.modulos.seguimiento', [
            'filas' => $filas,
            'capacitaciones' => $capacitaciones,
            'candidatosParaAgregar' => $candidatosParaAgregar,
        ]);
    }
}
