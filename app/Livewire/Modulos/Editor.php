<?php

namespace App\Livewire\Modulos;

use App\Models\Categoria;
use App\Models\Modulo;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Editor extends Component
{
    use WithFileUploads;

    public Modulo $modulo;

    // ─── Datos del módulo ───────────────────────────────────────────────────
    public string $titulo = '';

    public string $categoria = '';

    public string $contenido = '';

    public int $umbralAprobacion = 7;

    public string $fechaLimite = '';

    public bool $activo = true;

    // Colapsado por defecto al entrar a un módulo ya existente -- solo se
    // expande a formulario editable cuando el instructor pulsa "Editar"
    // (2026-07-16, antes se veía siempre todo el formulario abierto).
    public bool $editandoDatos = false;

    // ─── Alta de pregunta ───────────────────────────────────────────────────
    public string $enunciado = '';

    public string $tipoPregunta = 'vf';

    public string $respuestaCorrecta = '1';

    /** @var string[] */
    public array $opciones = ['', ''];

    public int $opcionCorrecta = 0;

    // ─── Revisión obligatoria y material de apoyo (diapositivas / PDF) ──────
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $nuevosArchivosObligatorios = [];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $nuevosArchivosApoyo = [];

    public function mount(Modulo $modulo): void
    {
        abort_unless(
            $modulo->created_by === auth()->id()
                || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
            403
        );

        $this->modulo = $modulo;
        $this->cargarDatosModulo();
    }

    private function cargarDatosModulo(): void
    {
        $this->titulo = $this->modulo->titulo;
        $this->categoria = $this->modulo->categoria;
        $this->contenido = (string) $this->modulo->contenido;
        $this->umbralAprobacion = $this->modulo->umbral_aprobacion;
        $this->fechaLimite = $this->modulo->fecha_limite?->format('Y-m-d') ?? '';
        $this->activo = $this->modulo->activo;
    }

    public function editarDatos(): void
    {
        $this->editandoDatos = true;
    }

    public function cancelarEdicionDatos(): void
    {
        // Descarta cualquier cambio sin guardar en los campos, no solo
        // colapsa la vista -- si no, al volver a pulsar "Editar" aparecería
        // lo que se había escrito antes de cancelar.
        $this->cargarDatosModulo();
        $this->resetValidation();
        $this->editandoDatos = false;
    }

    public function save(): void
    {
        $data = $this->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'categoria' => ['required', 'string', 'in:'.implode(',', Categoria::pluck('nombre')->all())],
            'contenido' => ['nullable', 'string'],
            'umbralAprobacion' => ['required', 'integer', 'min:1', 'max:10'],
            'fechaLimite' => ['nullable', 'date'],
            'activo' => ['boolean'],
        ], [], [
            'titulo' => 'título',
            'categoria' => 'categoría',
            'umbralAprobacion' => 'umbral de aprobación',
            'fechaLimite' => 'fecha límite',
        ]);

        $this->modulo->update([
            'titulo' => $data['titulo'],
            'categoria' => $data['categoria'],
            'contenido' => Modulo::limpiarContenido($data['contenido']),
            'umbral_aprobacion' => $data['umbralAprobacion'],
            'fecha_limite' => $data['fechaLimite'] ?: null,
            'activo' => $data['activo'],
        ]);

        $this->editandoDatos = false;
        session()->flash('success', 'Módulo actualizado.');
    }

    public function addOpcion(): void
    {
        $this->opciones[] = '';
    }

    public function removeOpcion(int $index): void
    {
        if (count($this->opciones) <= 2) {
            return;
        }

        unset($this->opciones[$index]);
        $this->opciones = array_values($this->opciones);

        if ($this->opcionCorrecta >= count($this->opciones)) {
            $this->opcionCorrecta = 0;
        }
    }

    public function addPregunta(): void
    {
        if ($this->tipoPregunta === 'opciones') {
            $data = $this->validate([
                'enunciado' => ['required', 'string', 'max:500'],
                'opciones' => ['required', 'array', 'min:2'],
                'opciones.*' => ['required', 'string', 'max:255'],
                'opcionCorrecta' => ['required', 'integer', 'min:0'],
            ], [], [
                'enunciado' => 'enunciado',
                'opciones.*' => 'opción',
            ]);

            if (! array_key_exists($data['opcionCorrecta'], $data['opciones'])) {
                $this->addError('opcionCorrecta', 'Selecciona cuál opción es la correcta.');

                return;
            }

            $pregunta = $this->modulo->preguntas()->create([
                'tipo' => 'opciones',
                'enunciado' => $data['enunciado'],
                'respuesta_correcta' => false,
                'orden' => $this->modulo->preguntas()->count() + 1,
            ]);

            foreach (array_values($data['opciones']) as $i => $texto) {
                $pregunta->opciones()->create([
                    'texto' => $texto,
                    'es_correcta' => $i === $data['opcionCorrecta'],
                    'orden' => $i + 1,
                ]);
            }
        } else {
            $data = $this->validate([
                'enunciado' => ['required', 'string', 'max:500'],
                'respuestaCorrecta' => ['required', 'in:0,1'],
            ], [], [
                'enunciado' => 'enunciado',
            ]);

            $this->modulo->preguntas()->create([
                'tipo' => 'vf',
                'enunciado' => $data['enunciado'],
                'respuesta_correcta' => (bool) $data['respuestaCorrecta'],
                'orden' => $this->modulo->preguntas()->count() + 1,
            ]);
        }

        $this->reset(['enunciado', 'opciones']);
        $this->respuestaCorrecta = '1';
        $this->opciones = ['', ''];
        $this->opcionCorrecta = 0;
        $this->resetValidation();
    }

    public function deletePregunta(int $preguntaId): void
    {
        $this->modulo->preguntas()->whereKey($preguntaId)->delete();
    }

    public function subirArchivosObligatorios(): void
    {
        $this->guardarArchivos('nuevosArchivosObligatorios', 'obligatorio');
    }

    public function subirArchivosApoyo(): void
    {
        $this->guardarArchivos('nuevosArchivosApoyo', 'apoyo');
    }

    private function guardarArchivos(string $propiedad, string $categoria): void
    {
        $this->validate([
            "$propiedad.*" => ['file', 'mimes:pdf,ppt,pptx,jpg,jpeg,png,webp,gif', 'max:25600'],
        ], [], [
            "$propiedad.*" => 'archivo',
        ]);

        foreach ($this->$propiedad as $archivo) {
            // Capturar esto ANTES de store(): mueve el archivo temporal, y
            // tras eso getClientOriginalName() ya no es confiable.
            $nombreOriginal = $archivo->getClientOriginalName();
            $extension = strtolower($archivo->getClientOriginalExtension());

            $path = $archivo->store('modulos/'.$this->modulo->id, 'local');

            // El tamaño se lee del archivo ya guardado en destino (no de
            // getSize() sobre el UploadedFile), que es la fuente confiable.
            $tamano = Storage::disk('local')->size($path);

            $this->modulo->archivos()->create([
                'categoria' => $categoria,
                'nombre_original' => $nombreOriginal,
                'disk_path' => $path,
                'tipo' => $extension,
                'tamano' => $tamano,
                'orden' => $this->modulo->archivos()->count() + 1,
                'created_by' => auth()->id(),
            ]);
        }

        $this->reset([$propiedad]);
        session()->flash('success', 'Material subido.');
    }

    public function eliminarArchivo(int $archivoId): void
    {
        $this->modulo->archivos()->whereKey($archivoId)->first()?->delete();
    }

    public function render()
    {
        $archivos = $this->modulo->archivos()->get();

        return view('livewire.modulos.editor', [
            'preguntas' => $this->modulo->preguntas()->with('opciones')->get(),
            'archivosObligatorios' => $archivos->reject->esApoyo(),
            'archivosApoyo' => $archivos->filter->esApoyo(),
            'categorias' => Categoria::orderBy('nombre')->pluck('nombre'),
        ]);
    }
}
