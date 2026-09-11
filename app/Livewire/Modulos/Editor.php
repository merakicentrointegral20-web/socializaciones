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

    public string $tipoInduccion = 'presencial';

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

    // ─── Videos (OneDrive embebido) ──────────────────────────────────────────
    public string $nuevoVideoTitulo = '';

    public string $nuevoVideoDescripcion = '';

    // Acepta tanto el link puro como el <iframe> completo que copia OneDrive
    // al usar "Insertar" -- se extrae el src en agregarVideo().
    public string $nuevoVideoUrl = '';

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
        $this->tipoInduccion = $this->modulo->tipo_induccion;
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
            'tipoInduccion' => ['required', 'in:presencial,virtual'],
            'contenido' => ['nullable', 'string'],
            'umbralAprobacion' => ['required', 'integer', 'min:1', 'max:10'],
            'fechaLimite' => ['nullable', 'date'],
            'activo' => ['boolean'],
        ], [], [
            'titulo' => 'título',
            'categoria' => 'categoría',
            'tipoInduccion' => 'tipo de inducción',
            'umbralAprobacion' => 'umbral de aprobación',
            'fechaLimite' => 'fecha límite',
        ]);

        $this->modulo->update([
            'titulo' => $data['titulo'],
            'categoria' => $data['categoria'],
            'tipo_induccion' => $data['tipoInduccion'],
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

    public function agregarVideo(): void
    {
        $data = $this->validate([
            'nuevoVideoTitulo' => ['required', 'string', 'max:150'],
            'nuevoVideoDescripcion' => ['nullable', 'string', 'max:1000'],
            'nuevoVideoUrl' => ['required', 'string'],
        ], [], [
            'nuevoVideoTitulo' => 'título',
            'nuevoVideoDescripcion' => 'descripción',
            'nuevoVideoUrl' => 'link del video',
        ]);

        // OneDrive/YouTube, al usar "Insertar", copian un <iframe> completo en
        // vez de un link suelto -- si detectamos eso, se extrae solo el src.
        $src = $data['nuevoVideoUrl'];

        if (str_contains($src, '<iframe') && preg_match('/<iframe[^>]*\bsrc="([^"]+)"/i', $src, $match)) {
            $src = $match[1];
        }

        $embedUrl = $this->normalizarUrlVideo(trim($src));

        if (! $embedUrl) {
            $this->addError('nuevoVideoUrl', 'Pega un link de OneDrive ("Insertar"), Google Drive, YouTube o Vimeo, o el código <iframe> completo.');

            return;
        }

        $this->modulo->videos()->create([
            'titulo' => $data['nuevoVideoTitulo'],
            'descripcion' => $data['nuevoVideoDescripcion'] ?: null,
            'embed_url' => $embedUrl,
            'orden' => $this->modulo->videos()->count() + 1,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['nuevoVideoTitulo', 'nuevoVideoDescripcion', 'nuevoVideoUrl']);
        $this->resetValidation();
        session()->flash('success', 'Video agregado.');
    }

    // Acepta OneDrive/SharePoint (ya vienen como link embebible, se usan tal
    // cual), y normaliza los links normales de "compartir" de YouTube/Vimeo/
    // Google Drive a su versión embebible -- un link de youtube.com/watch,
    // vimeo.com/123 o drive.google.com/.../view no se puede meter directo en
    // un <iframe>, el sitio lo bloquea.
    private function normalizarUrlVideo(string $src): ?string
    {
        if (! filter_var($src, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower((string) parse_url($src, PHP_URL_HOST));

        if (str_contains($host, 'onedrive') || str_contains($host, 'sharepoint')) {
            return $src;
        }

        if (str_contains($host, 'youtube.com') || $host === 'youtu.be') {
            return $this->embedDeYoutube($src);
        }

        if (str_contains($host, 'vimeo.com')) {
            return $this->embedDeVimeo($src);
        }

        if (str_contains($host, 'drive.google.com')) {
            return $this->embedDeGoogleDrive($src);
        }

        return null;
    }

    private function embedDeYoutube(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $id = match (true) {
            $host === 'youtu.be' => trim($path, '/'),
            str_starts_with($path, '/embed/') => substr($path, 7),
            str_starts_with($path, '/shorts/') => substr($path, 8),
            isset($query['v']) => $query['v'],
            default => null,
        };

        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    private function embedDeVimeo(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if (str_starts_with($path, '/video/')) {
            return 'https://player.vimeo.com'.$path;
        }

        if (preg_match('#^/(\d+)$#', $path, $match)) {
            return "https://player.vimeo.com/video/{$match[1]}";
        }

        return null;
    }

    // Un link de "Compartir" de Google Drive (/file/d/ID/view) no se puede
    // meter directo en un <iframe>, hay que usar la versión "/preview".
    private function embedDeGoogleDrive(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if (preg_match('#^/file/d/([^/]+)#', $path, $match)) {
            return "https://drive.google.com/file/d/{$match[1]}/preview";
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (isset($query['id'])) {
            return "https://drive.google.com/file/d/{$query['id']}/preview";
        }

        return null;
    }

    public function eliminarVideo(int $videoId): void
    {
        $this->modulo->videos()->whereKey($videoId)->first()?->delete();
        session()->flash('success', 'Video eliminado.');
    }

    public function moverVideoArriba(int $videoId): void
    {
        $this->intercambiarOrdenVideo($videoId, -1);
    }

    public function moverVideoAbajo(int $videoId): void
    {
        $this->intercambiarOrdenVideo($videoId, 1);
    }

    private function intercambiarOrdenVideo(int $videoId, int $direccion): void
    {
        $videos = $this->modulo->videos()->get();
        $indice = $videos->search(fn ($v) => $v->id === $videoId);

        if ($indice === false) {
            return;
        }

        $indiceVecino = $indice + $direccion;

        if (! $videos->has($indiceVecino)) {
            return;
        }

        $actual = $videos->get($indice);
        $vecino = $videos->get($indiceVecino);

        [$ordenActual, $ordenVecino] = [$actual->orden, $vecino->orden];

        $actual->update(['orden' => $ordenVecino]);
        $vecino->update(['orden' => $ordenActual]);
    }

    public function render()
    {
        $archivos = $this->modulo->archivos()->get();

        return view('livewire.modulos.editor', [
            'preguntas' => $this->modulo->preguntas()->with('opciones')->get(),
            'archivosObligatorios' => $archivos->reject->esApoyo(),
            'archivosApoyo' => $archivos->filter->esApoyo(),
            'videos' => $this->modulo->videos()->get(),
            'categorias' => Categoria::orderBy('nombre')->pluck('nombre'),
        ]);
    }
}
