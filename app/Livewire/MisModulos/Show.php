<?php

namespace App\Livewire\MisModulos;

use App\Models\CapacitacionAsistente;
use App\Models\Certificado;
use App\Models\EvaluacionIntento;
use App\Models\Modulo;
use App\Models\ModuloLectura;
use App\Models\ModuloVideoProgreso;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Modulo $modulo;

    public bool $yaLeido = false;

    public bool $documentoAbierto = false;

    public ?CapacitacionAsistente $induccion = null;

    public bool $asistioInduccion = false;

    public ?EvaluacionIntento $ultimoIntento = null;

    public ?Certificado $certificado = null;

    public ?string $leidoAt = null;

    public function mount(Modulo $modulo): void
    {
        abort_unless($modulo->activo, 404);
        abort_unless($modulo->visiblePara(auth()->user()), 404);

        $this->modulo = $modulo;

        $lectura = ModuloLectura::where('modulo_id', $modulo->id)
            ->where('user_id', auth()->id())
            ->first();

        $this->yaLeido = (bool) $lectura?->leido_at;
        $this->documentoAbierto = (bool) $lectura?->documento_abierto_at;
        $this->leidoAt = $lectura?->leido_at?->toDateTimeString();

        $this->induccion = CapacitacionAsistente::query()
            ->where('user_id', auth()->id())
            ->whereHas('capacitacion', fn ($q) => $q->where('modulo_id', $modulo->id))
            ->with('capacitacion')
            ->get()
            ->sortByDesc(fn (CapacitacionAsistente $a) => $a->capacitacion->fecha)
            ->first();

        $this->asistioInduccion = CapacitacionAsistente::where('user_id', auth()->id())
            ->where('asistio', true)
            ->whereHas('capacitacion', fn ($q) => $q->where('modulo_id', $modulo->id))
            ->exists();

        $this->ultimoIntento = EvaluacionIntento::where('user_id', auth()->id())
            ->where('modulo_id', $modulo->id)
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->first();

        $this->certificado = Certificado::where('user_id', auth()->id())
            ->where('modulo_id', $modulo->id)
            ->first();
    }

    public function abrirDocumento(): void
    {
        $this->documentoAbierto = true;

        ModuloLectura::updateOrCreate(
            ['modulo_id' => $this->modulo->id, 'user_id' => auth()->id()],
            ['documento_abierto_at' => now()]
        );
    }

    public function marcarLeido(): void
    {
        $ahora = now();

        ModuloLectura::updateOrCreate(
            ['modulo_id' => $this->modulo->id, 'user_id' => auth()->id()],
            ['leido_at' => $ahora]
        );

        $this->yaLeido = true;
        $this->leidoAt = $ahora->toDateTimeString();
        session()->flash('success', 'Marcaste este módulo como leído. Ya puedes ver los videos del módulo.');
    }

    // Cuando se agotan los 3 intentos de evaluación, la única forma de volver
    // a intentar es reiniciar el módulo: se borra el progreso de lectura y
    // videos (así se resiembra el ciclo de intentos, ver
    // Evaluacion::MAX_INTENTOS), pero el historial de EvaluacionIntento se
    // conserva -- es historial de la persona, no se puede perder.
    public function reiniciarModulo(): void
    {
        if (! $this->intentosAgotados()) {
            return;
        }

        ModuloLectura::where('modulo_id', $this->modulo->id)
            ->where('user_id', auth()->id())
            ->update(['leido_at' => null, 'documento_abierto_at' => null]);

        ModuloVideoProgreso::whereIn('modulo_video_id', $this->modulo->videos()->pluck('id'))
            ->where('user_id', auth()->id())
            ->delete();

        $this->yaLeido = false;
        $this->documentoAbierto = false;
        $this->leidoAt = null;

        session()->flash('success', 'Módulo reiniciado. Vuelve a leer el contenido y ver los videos para intentar la evaluación de nuevo.');
    }

    private function intentosAgotados(): bool
    {
        if (! $this->yaLeido || $this->certificado) {
            return false;
        }

        return EvaluacionIntento::where('user_id', auth()->id())
            ->where('modulo_id', $this->modulo->id)
            ->where('completed_at', '>=', $this->leidoAt)
            ->count() >= Evaluacion::MAX_INTENTOS;
    }

    public function marcarVideoCompletado(int $videoId): void
    {
        if (! $this->yaLeido) {
            return;
        }

        $videos = $this->modulo->videos;
        $completadosIds = ModuloVideoProgreso::whereIn('modulo_video_id', $videos->pluck('id'))
            ->where('user_id', auth()->id())
            ->whereNotNull('completado_at')
            ->pluck('modulo_video_id')
            ->all();

        // El primero de la lista que todavía no está completado es el único que
        // se puede marcar ahora -- nunca se confía en qué id mandó el front.
        $siguienteDesbloqueado = $videos->first(fn ($v) => ! in_array($v->id, $completadosIds, true));

        if (! $siguienteDesbloqueado || $siguienteDesbloqueado->id !== $videoId) {
            return;
        }

        ModuloVideoProgreso::updateOrCreate(
            ['modulo_video_id' => $videoId, 'user_id' => auth()->id()],
            ['completado_at' => now()]
        );
    }

    public function render()
    {
        $archivos = $this->modulo->archivos()->get();
        $obligatorios = $archivos->reject->esApoyo();

        $documentos = $obligatorios->reject(fn ($archivo) => $archivo->esImagen());

        $videosModulo = $this->modulo->videos;
        $completadosIds = ModuloVideoProgreso::whereIn('modulo_video_id', $videosModulo->pluck('id'))
            ->where('user_id', auth()->id())
            ->whereNotNull('completado_at')
            ->pluck('modulo_video_id')
            ->all();

        $yaLlegoAlBloqueado = false;
        $videos = $videosModulo->map(function ($video) use ($completadosIds, &$yaLlegoAlBloqueado) {
            $completado = in_array($video->id, $completadosIds, true);
            $desbloqueado = $this->yaLeido && ! $completado && ! $yaLlegoAlBloqueado;

            if (! $completado) {
                $yaLlegoAlBloqueado = true;
            }

            return (object) [
                'video' => $video,
                'completado' => $completado,
                'desbloqueado' => $completado || $desbloqueado,
            ];
        });

        $todosLosVideosCompletados = $this->modulo->todosLosVideosCompletados(auth()->id());

        $intentosEnCiclo = $this->yaLeido
            ? EvaluacionIntento::where('user_id', auth()->id())
                ->where('modulo_id', $this->modulo->id)
                ->where('completed_at', '>=', $this->leidoAt)
                ->count()
            : 0;

        return view('livewire.mis-modulos.show', [
            'imagenes' => $obligatorios->filter(fn ($archivo) => $archivo->esImagen()),
            'documentos' => $documentos,
            'requiereAbrirDocumento' => $documentos->isNotEmpty(),
            'videos' => $videos,
            'todosLosVideosCompletados' => $todosLosVideosCompletados,
            'intentosRestantes' => max(0, Evaluacion::MAX_INTENTOS - $intentosEnCiclo),
            'intentosAgotados' => $intentosEnCiclo >= Evaluacion::MAX_INTENTOS,
        ]);
    }
}
