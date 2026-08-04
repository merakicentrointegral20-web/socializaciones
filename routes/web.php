<?php

use App\Livewire\Colaboradores\GestionAcceso;
use App\Livewire\Dashboard;
use App\Livewire\MisModulos\Evaluacion as MisModulosEvaluacion;
use App\Livewire\MisModulos\Index as MisModulosIndex;
use App\Livewire\MisModulos\Show as MisModulosShow;
use App\Livewire\Modulos\Editor as ModulosEditor;
use App\Livewire\Modulos\Index as ModulosIndex;
use App\Livewire\Modulos\Seguimiento as ModulosSeguimiento;
use App\Models\Certificado;
use App\Models\Modulo;
use App\Models\ModuloArchivo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->rutaInicio())
        : redirect()->route('login');
});

// Panel de pendientes para quien crea módulos (qué le falta a quién). Varios
// flujos de Breeze (verificación de email, confirmar contraseña, etc.)
// también redirigen aquí después de completarse -- por eso, si quien entra
// no puede crear módulos, el propio componente lo manda a Mis módulos.
Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified', 'elevated.2fa'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'elevated.2fa'])
    ->name('profile');

Route::get('colaboradores', GestionAcceso::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:colaboradores.gestionar'])
    ->name('colaboradores.index');

// Exportación de solo lectura: nunca escribe nada, así que no hay riesgo de
// tocar la "base verdad" compartida con xuri-apex. Incluye tanto a quien ya
// tiene cuenta como a los colaboradores de bodega/CEDI que todavía no la
// tienen (mismas dos listas que ve GestionAcceso). Es la foto completa para
// respaldo/consulta -- para trabajar con los datos, usar una de las dos
// plantillas de abajo (activación o actualización), que están recortadas a
// exactamente lo que cada una necesita.
Route::get('colaboradores/exportar', function () {
    $filename = 'colaboradores-'.now()->format('Y-m-d_His').'.csv';

    return response()->streamDownload(function () {
        $out = fopen('php://output', 'w');
        // BOM para que Excel en Windows detecte UTF-8 y no rompa acentos/ñ.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['nombre', 'email', 'tiene_cuenta', 'area', 'codigo', 'cedula', 'funcion_cargo', 'telefono', 'fecha_nacimiento', 'roles']);

        \App\Models\User::query()
            ->with(['collaborator', 'contacto', 'roles'])
            ->orderBy('name')
            ->chunk(200, function ($people) use ($out) {
                foreach ($people as $person) {
                    $esProduccion = (bool) $person->collaborator;

                    fputcsv($out, [
                        $person->name,
                        $person->email,
                        'si',
                        $esProduccion ? 'produccion' : 'administracion',
                        $person->collaborator->code ?? '',
                        $person->collaborator->id_card ?? $person->contacto->cedula ?? '',
                        $person->contacto->cargo ?? ($person->collaborator->function ?? ''),
                        $person->contacto->telefono ?? '',
                        optional($person->collaborator?->birth_date)->format('Y-m-d'),
                        $person->roles->pluck('name')->implode('|'),
                    ]);
                }
            });

        \App\Models\Collaborator::query()
            ->whereNull('user_id')
            ->orderBy('last_name')
            ->chunk(200, function ($pendientes) use ($out) {
                foreach ($pendientes as $collaborator) {
                    fputcsv($out, [
                        $collaborator->fullName(),
                        $collaborator->email ?? '',
                        'no',
                        'produccion',
                        $collaborator->code ?? '',
                        $collaborator->id_card ?? '',
                        $collaborator->function ?? '',
                        '',
                        optional($collaborator->birth_date)->format('Y-m-d'),
                        '',
                    ]);
                }
            });

        fclose($out);
    }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
})->middleware(['auth', 'verified', 'elevated.2fa', 'can:colaboradores.gestionar'])->name('colaboradores.exportar');

// Plantilla de ACTIVACIÓN -- lo único que crea cuentas. Trae a todos los de
// "Sin cuenta todavía" (nombre solo de referencia, no se usa al importar) y
// deja email/password/activo en blanco para que RRHH los decida. Solo la
// fila con activo=si se procesa -- así se puede preparar la lista completa
// y activar de a poco sin re-descargar nada; GestionAcceso::importActivacion
// vuelve a detectar por cédula, así que una fila ya activada nunca duplica
// ni regenera su cuenta si se resube el mismo archivo.
Route::get('colaboradores/plantilla-activacion', function () {
    return response()->streamDownload(function () {
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['nombre', 'cedula', 'email', 'password', 'activo']);

        \App\Models\Collaborator::query()
            ->whereNull('user_id')
            ->orderBy('last_name')
            ->chunk(200, function ($pendientes) use ($out) {
                foreach ($pendientes as $collaborator) {
                    fputcsv($out, [$collaborator->fullName(), $collaborator->id_card ?? '', '', '', 'no']);
                }
            });

        fclose($out);
    }, 'plantilla-activacion.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
})->middleware(['auth', 'verified', 'elevated.2fa', 'can:colaboradores.gestionar'])->name('colaboradores.plantilla-activacion');

// Plantilla de ACTUALIZACIÓN -- lo único que edita datos, y solo de quien YA
// tiene cuenta. Recortada a lo que xr-rrhh puede escribir sin pisar la BD
// compartida con xuri-apex (celular, cargo, cédula -- ver mysql-app-user.sql):
// nombre/correo/función quedan de solo lectura por ahora. Se identifica por
// cédula (es única), nunca por email, para no depender de que el correo ya
// esté bien escrito.
Route::get('colaboradores/plantilla-actualizacion', function () {
    return response()->streamDownload(function () {
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['nombre', 'cedula', 'telefono', 'cargo']);

        \App\Models\User::query()
            ->with(['collaborator', 'contacto'])
            ->orderBy('name')
            ->chunk(200, function ($people) use ($out) {
                foreach ($people as $person) {
                    $cedula = $person->collaborator->id_card ?? $person->contacto->cedula ?? '';

                    if ($cedula === '') {
                        // No hay forma de volver a encontrar esta fila al reimportar
                        // (nada la identifica todavía) -- se omite en vez de generar
                        // una fila que siempre va a salir en "errores".
                        continue;
                    }

                    fputcsv($out, [
                        $person->name,
                        $cedula,
                        $person->contacto->telefono ?? '',
                        $person->contacto->cargo ?? ($person->collaborator->function ?? ''),
                    ]);
                }
            });

        fclose($out);
    }, 'plantilla-actualizacion.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
})->middleware(['auth', 'verified', 'elevated.2fa', 'can:colaboradores.gestionar'])->name('colaboradores.plantilla-actualizacion');

Route::get('modulos', ModulosIndex::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:modulos.crear'])
    ->name('modulos.index');

Route::get('modulos/{modulo}', ModulosEditor::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:modulos.crear'])
    ->name('modulos.show');

Route::get('modulos/{modulo}/seguimiento', ModulosSeguimiento::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:modulos.crear'])
    ->name('modulos.seguimiento');

// Descarga de materiales: cualquier usuario autenticado puede ver los
// archivos de cualquier módulo (no solo instructores) -- es justamente
// lo que el colaborador necesita al leer un módulo.
// Imágenes y PDF se sirven inline (el PDF se abre en otra pestaña con el
// visor nativo del navegador, sin forzar descarga). PPT/PPTX sí se
// descargan porque el navegador no los puede previsualizar.
Route::get('modulos/{modulo}/archivos/{archivo}', function (Modulo $modulo, ModuloArchivo $archivo) {
    abort_unless($archivo->modulo_id === $modulo->id, 404);

    return ($archivo->esImagen() || $archivo->tipo === 'pdf')
        ? Storage::disk('local')->response($archivo->disk_path, $archivo->nombre_original)
        : Storage::disk('local')->download($archivo->disk_path, $archivo->nombre_original);
})->middleware(['auth', 'verified'])->name('modulos.archivos.download');

// Acta de inducción: solo el instructor dueño del módulo la puede ver.
Route::get('modulos/{modulo}/capacitaciones/{capacitacion}/acta', function (Modulo $modulo, \App\Models\Capacitacion $capacitacion) {
    abort_unless($modulo->created_by === auth()->id(), 403);
    abort_unless($capacitacion->modulo_id === $modulo->id, 404);
    abort_unless($capacitacion->acta_path, 404);

    return Storage::disk('local')->response($capacitacion->acta_path);
})->middleware(['auth', 'verified'])->name('capacitaciones.acta');

// Acta de asistencia en blanco (Nombre y Apellido / Cédula / Firma) para
// imprimir antes de la sesión y recoger firmas a mano -- se genera al vuelo,
// nunca se guarda en disco (el acta "real" es la escaneada que se sube en
// capacitaciones.acta arriba).
Route::get('modulos/{modulo}/capacitaciones/{capacitacion}/acta/imprimir', function (Modulo $modulo, \App\Models\Capacitacion $capacitacion) {
    abort_unless($modulo->created_by === auth()->id(), 403);
    abort_unless($capacitacion->modulo_id === $modulo->id, 404);

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.acta-induccion', [
        'capacitacion' => $capacitacion->load('modulo', 'instructor'),
        'asistentes' => $capacitacion->asistentes()->with('user')->get()->sortBy(fn ($a) => $a->user->name)->values(),
    ])->setPaper('a4', 'portrait');

    return $pdf->stream("acta-induccion-{$capacitacion->id}.pdf");
})->middleware(['auth', 'verified'])->name('capacitaciones.acta-imprimir');

Route::get('mis-modulos', MisModulosIndex::class)
    ->middleware(['auth', 'verified', 'elevated.2fa'])
    ->name('mis-modulos.index');

Route::get('mis-modulos/{modulo}', MisModulosShow::class)
    ->middleware(['auth', 'verified', 'elevated.2fa'])
    ->name('mis-modulos.show');

Route::get('mis-modulos/{modulo}/evaluacion', MisModulosEvaluacion::class)
    ->middleware(['auth', 'verified', 'elevated.2fa'])
    ->name('mis-modulos.evaluacion');

// El certificado lo puede descargar el propio colaborador o el instructor
// dueño del módulo (el mismo criterio que ya usa Seguimiento).
Route::get('certificados/{certificado}/pdf', function (Certificado $certificado) {
    abort_unless(
        $certificado->user_id === auth()->id() || $certificado->modulo->created_by === auth()->id(),
        403
    );
    abort_unless($certificado->pdf_path && Storage::disk('local')->exists($certificado->pdf_path), 404);

    return Storage::disk('local')->response($certificado->pdf_path, "certificado-{$certificado->numero}.pdf");
})->middleware(['auth', 'verified'])->name('certificados.pdf');

require __DIR__.'/auth.php';
