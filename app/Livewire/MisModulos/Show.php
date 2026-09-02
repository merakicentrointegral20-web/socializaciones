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
class Show extends Component
{
    public Modulo $modulo;

    public bool $yaLeido = false;

    public bool $documentoAbierto = false;

    public ?CapacitacionAsistente $induccion = null;

    public bool $asistioInduccion = false;

    public ?EvaluacionIntento $ultimoIntento = null;

    public ?Certificado $certificado = null;

    public function mount(Modulo $modulo): void
    {
        abort_unless($modulo->activo, 404);

        $this->modulo = $modulo;

        $lectura = ModuloLectura::where('modulo_id', $modulo->id)
            ->where('user_id', auth()->id())
            ->first();

        $this->yaLeido = (bool) $lectura?->leido_at;
        $this->documentoAbierto = (bool) $lectura?->documento_abierto_at;

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
        ModuloLectura::updateOrCreate(
            ['modulo_id' => $this->modulo->id, 'user_id' => auth()->id()],
            ['leido_at' => now()]
        );

        $this->yaLeido = true;
        session()->flash('success', 'Marcaste este módulo como leído. Ya puedes rendir tu evaluación.');
    }

    public function render()
    {
        $archivos = $this->modulo->archivos()->get();
        $obligatorios = $archivos->reject->esApoyo();

        $documentos = $obligatorios->reject(fn ($archivo) => $archivo->esImagen());

        return view('livewire.mis-modulos.show', [
            'imagenes' => $obligatorios->filter(fn ($archivo) => $archivo->esImagen()),
            'documentos' => $documentos,
            'requiereAbrirDocumento' => $documentos->isNotEmpty(),
        ]);
    }
}
