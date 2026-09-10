<?php

namespace App\Livewire\MisModulos;

use App\Models\CapacitacionAsistente;
use App\Models\Certificado;
use App\Models\EvaluacionIntento;
use App\Models\Modulo;
use App\Models\ModuloLectura;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Evaluacion extends Component
{
    public Modulo $modulo;

    /** @var array<int, string> */
    public array $respuestas = [];

    /** Orden (ids de pregunta) sorteado para este intento, para que no se reordene en cada render. */
    public array $ordenPreguntas = [];

    // Cuántas veces se puede intentar la evaluación antes de exigir reiniciar
    // el módulo (volver a leer y ver los videos) -- ver
    // App\Livewire\MisModulos\Show::reiniciarModulo().
    public const MAX_INTENTOS = 3;

    public function mount(Modulo $modulo): void
    {
        abort_unless($modulo->activo, 404);
        abort_unless($modulo->visiblePara(auth()->user()), 404);

        $lectura = ModuloLectura::where('user_id', auth()->id())
            ->where('modulo_id', $modulo->id)
            ->first();

        abort_unless($lectura?->leido_at, 403, 'Todavía no marcaste este módulo como leído.');

        abort_unless(
            $modulo->todosLosVideosCompletados(auth()->id()),
            403,
            'Todavía no completaste todos los videos de este módulo.'
        );

        $yaAprobado = EvaluacionIntento::where('user_id', auth()->id())
            ->where('modulo_id', $modulo->id)
            ->where('aprobado', true)
            ->exists();

        abort_if($yaAprobado, 403, 'Ya aprobaste la evaluación de este módulo.');

        // Solo cuentan los intentos desde la última vez que se marcó como
        // leído -- reiniciar el módulo "abre" otra tanda de intentos.
        $intentosEnCiclo = EvaluacionIntento::where('user_id', auth()->id())
            ->where('modulo_id', $modulo->id)
            ->where('completed_at', '>=', $lectura->leido_at)
            ->count();

        abort_if(
            $intentosEnCiclo >= self::MAX_INTENTOS,
            403,
            'Alcanzaste el máximo de '.self::MAX_INTENTOS.' intentos para este módulo. Debes reiniciar el módulo (leer el contenido y ver los videos de nuevo) para volver a intentar la evaluación.'
        );

        $this->modulo = $modulo;

        $this->ordenPreguntas = $modulo->preguntas()->pluck('id')->shuffle()->values()->all();
    }

    public function enviar(): void
    {
        $preguntas = $this->modulo->preguntas()->with('opciones')->get();

        foreach ($preguntas as $pregunta) {
            if (! isset($this->respuestas[$pregunta->id]) || $this->respuestas[$pregunta->id] === '') {
                $this->addError('respuestas', 'Responde todas las preguntas antes de enviar.');

                return;
            }
        }

        $correctas = 0;

        foreach ($preguntas as $pregunta) {
            $respuesta = $this->respuestas[$pregunta->id];

            if ($pregunta->tipo === 'opciones') {
                $opcionCorrecta = $pregunta->opciones->firstWhere('es_correcta', true);

                if ($opcionCorrecta && (int) $respuesta === $opcionCorrecta->id) {
                    $correctas++;
                }
            } elseif ((bool) $respuesta === (bool) $pregunta->respuesta_correcta) {
                $correctas++;
            }
        }

        $total = $preguntas->count();
        $notaSobre10 = $total > 0 ? (int) round(($correctas / $total) * 10) : 0;
        $aprobado = $notaSobre10 >= $this->modulo->umbral_aprobacion;

        $capacitacionId = CapacitacionAsistente::where('user_id', auth()->id())
            ->where('asistio', true)
            ->whereHas('capacitacion', fn ($q) => $q->where('modulo_id', $this->modulo->id))
            ->with('capacitacion')
            ->get()
            ->sortByDesc(fn (CapacitacionAsistente $a) => $a->capacitacion->fecha)
            ->first()
            ?->capacitacion_id;

        $intentoNumero = EvaluacionIntento::where('user_id', auth()->id())
            ->where('modulo_id', $this->modulo->id)
            ->count() + 1;

        $intento = EvaluacionIntento::create([
            'user_id' => auth()->id(),
            'modulo_id' => $this->modulo->id,
            'capacitacion_id' => $capacitacionId,
            'score' => $correctas,
            'total_preguntas' => $total,
            'aprobado' => $aprobado,
            'intento_numero' => $intentoNumero,
            'completed_at' => now(),
        ]);

        if ($aprobado) {
            $certificado = Certificado::firstOrCreate(
                ['user_id' => auth()->id(), 'modulo_id' => $this->modulo->id],
                ['evaluacion_intento_id' => $intento->id, 'emitido_at' => now()]
            );

            if (! $certificado->pdf_path) {
                $certificado->generarPdf();
            }
        }

        if ($aprobado) {
            $mensaje = "¡Aprobaste! Nota: {$notaSobre10}/10. Tu certificado ya está disponible.";
        } else {
            $lectura = ModuloLectura::where('user_id', auth()->id())->where('modulo_id', $this->modulo->id)->first();
            $intentosEnCiclo = EvaluacionIntento::where('user_id', auth()->id())
                ->where('modulo_id', $this->modulo->id)
                ->where('completed_at', '>=', $lectura->leido_at)
                ->count();
            $restantes = max(0, self::MAX_INTENTOS - $intentosEnCiclo);

            $mensaje = $restantes > 0
                ? "No alcanzaste el umbral. Nota: {$notaSobre10}/10. Te ".($restantes === 1 ? 'queda 1 intento' : "quedan {$restantes} intentos").'.'
                : "No alcanzaste el umbral. Nota: {$notaSobre10}/10. Alcanzaste el máximo de intentos: debes reiniciar el módulo para volver a intentarlo.";
        }

        session()->flash('success', $mensaje);

        $this->redirect(route('mis-modulos.show', $this->modulo), navigate: true);
    }

    public function render()
    {
        $orden = $this->ordenPreguntas;

        $preguntas = $this->modulo->preguntas()->with('opciones')->get()
            ->sortBy(fn ($pregunta) => array_search($pregunta->id, $orden))
            ->values();

        return view('livewire.mis-modulos.evaluacion', [
            'preguntas' => $preguntas,
        ]);
    }
}
