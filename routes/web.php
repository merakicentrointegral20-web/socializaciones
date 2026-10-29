<?php

use App\Livewire\Colaboradores\GestionAcceso;
use App\Livewire\Dashboard;
use App\Livewire\MisModulos\Evaluacion as MisModulosEvaluacion;
use App\Livewire\MisModulos\Index as MisModulosIndex;
use App\Livewire\MisModulos\Show as MisModulosShow;
use App\Livewire\Modulos\Asignacion as ModulosAsignacion;
use App\Livewire\Modulos\Editor as ModulosEditor;
use App\Livewire\Modulos\Index as ModulosIndex;
use App\Livewire\Modulos\Seguimiento as ModulosSeguimiento;
use App\Models\Certificado;
use App\Models\Modulo;
use App\Models\ModuloArchivo;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

// Roster de solo lectura -- cuentas, cédula, celular y área se gestionan
// directamente en Xuri Apex (ver App\Livewire\Colaboradores\GestionAcceso).
// Lo único que se administra desde acá es el rol de instructor.
Route::get('colaboradores', GestionAcceso::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:colaboradores.gestionar'])
    ->name('colaboradores.index');

Route::get('modulos', ModulosIndex::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:modulos.crear'])
    ->name('modulos.index');

Route::get('modulos/{modulo}', ModulosEditor::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:modulos.crear'])
    ->name('modulos.show');

Route::get('modulos/{modulo}/seguimiento', ModulosSeguimiento::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:modulos.crear'])
    ->name('modulos.seguimiento');

Route::get('modulos/{modulo}/asignacion', ModulosAsignacion::class)
    ->middleware(['auth', 'verified', 'elevated.2fa', 'can:modulos.crear'])
    ->name('modulos.asignacion');

// Acta de asignación: a diferencia del acta de inducción (en blanco, para
// firmar en papel antes de la sesión), acá la cédula sí va rellenada -- ya
// se sabe quién quedó asignado al momento de imprimir. Se calcula en vivo a
// partir de la asignación actual del módulo, nunca se guarda en disco.
Route::get('modulos/{modulo}/asignacion/acta', function (Modulo $modulo) {
    abort_unless(
        $modulo->created_by === auth()->id()
            || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
        403
    );

    $colaboradores = $modulo->asignado_a_todos
        ? User::participantes()->orderBy('name')->get()
        : $modulo->asignaciones()->with('user.collaborator', 'user.roles')->get()
            ->pluck('user')->filter()->reject(fn (User $u) => $u->esSoloSupervision())
            ->sortBy('name')->values();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.acta-asignacion', [
        'modulo' => $modulo,
        'colaboradores' => $colaboradores,
    ])->setPaper('a4', 'portrait');

    return $pdf->stream("acta-asignacion-{$modulo->id}.pdf");
})->middleware(['auth', 'verified'])->name('modulos.asignacion.acta');

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

// Acta de inducción: el instructor dueño del módulo o quien tenga permiso
// de administrar accesos la puede ver (mismo criterio que Seguimiento::mount).
Route::get('modulos/{modulo}/capacitaciones/{capacitacion}/acta', function (Modulo $modulo, \App\Models\Capacitacion $capacitacion) {
    abort_unless(
        $modulo->created_by === auth()->id()
            || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
        403
    );
    abort_unless($capacitacion->modulo_id === $modulo->id, 404);
    abort_unless($capacitacion->acta_path, 404);

    return Storage::disk('local')->response($capacitacion->acta_path);
})->middleware(['auth', 'verified'])->name('capacitaciones.acta');

// Acta de asistencia en blanco (Nombre y Apellido / Cédula / Firma) para
// imprimir antes de la sesión y recoger firmas a mano -- se genera al vuelo,
// nunca se guarda en disco (el acta "real" es la escaneada que se sube en
// capacitaciones.acta arriba).
Route::get('modulos/{modulo}/capacitaciones/{capacitacion}/acta/imprimir', function (Modulo $modulo, \App\Models\Capacitacion $capacitacion) {
    abort_unless(
        $modulo->created_by === auth()->id()
            || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
        403
    );
    abort_unless($capacitacion->modulo_id === $modulo->id, 404);

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.acta-induccion', [
        'capacitacion' => $capacitacion->load('modulo', 'instructor'),
        'asistentes' => $capacitacion->asistentes()->with('user.roles')->get()
            ->reject(fn ($a) => $a->user->esSoloSupervision())
            ->sortBy(fn ($a) => $a->user->name)->values(),
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

// Versión por período de un certificado (ver config('xr_rrhh.certificado_periodos')),
// armada al vuelo. Mismo criterio de acceso que el PDF original.
Route::get('certificados/{certificado}/periodo/{periodo}/pdf', function (Certificado $certificado, int $periodo) {
    abort_unless(
        $certificado->user_id === auth()->id() || $certificado->modulo->created_by === auth()->id(),
        403
    );
    abort_unless(array_key_exists($periodo, config('xr_rrhh.certificado_periodos')), 404);

    return response($certificado->pdfPeriodo($periodo), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => "inline; filename=\"certificado-{$periodo}-{$certificado->numero}.pdf\"",
    ]);
})->whereNumber('periodo')->middleware(['auth', 'verified'])->name('certificados.periodo.pdf');

// Descarga en lote de todos los certificados emitidos de un módulo, en un
// ZIP (un PDF por persona). Mismo criterio de acceso que Seguimiento. Si a
// algún certificado le falta el PDF en disco, se regenera al vuelo.
Route::get('modulos/{modulo}/certificados/zip', function (Modulo $modulo) {
    abort_unless(
        $modulo->created_by === auth()->id()
            || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
        403
    );

    $certificados = $modulo->certificados()->with('user', 'modulo')->get()
        ->sortBy(fn (Certificado $c) => $c->user->name);

    if ($certificados->isEmpty()) {
        return back()->with('error', 'Este módulo todavía no tiene certificados emitidos.');
    }

    $zipPath = tempnam(sys_get_temp_dir(), 'cert');
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($certificados as $certificado) {
        if (! $certificado->pdf_path || ! Storage::disk('local')->exists($certificado->pdf_path)) {
            $certificado->generarPdf();
        }

        $nombre = Str::slug($certificado->user->name, '_');
        $zip->addFromString(
            "certificado-{$certificado->numero}-{$nombre}.pdf",
            Storage::disk('local')->get($certificado->pdf_path)
        );
    }

    $zip->close();

    return response()->download($zipPath, 'certificados-'.Str::slug($modulo->titulo).'.zip')
        ->deleteFileAfterSend();
})->middleware(['auth', 'verified'])->name('modulos.certificados.zip');

// Igual que el anterior, pero con la versión por período del certificado
// (Duración + Período + su propia fecha de emisión, ver
// config('xr_rrhh.certificado_periodos')). Se arma al vuelo, no se guarda.
Route::get('modulos/{modulo}/certificados/periodo/{periodo}/zip', function (Modulo $modulo, int $periodo) {
    abort_unless(
        $modulo->created_by === auth()->id()
            || auth()->user()->hasPermissionTo(config('xr_rrhh.manage_access_permission')),
        403
    );
    abort_unless(array_key_exists($periodo, config('xr_rrhh.certificado_periodos')), 404);

    $certificados = $modulo->certificados()->with('user', 'modulo')->get()
        ->sortBy(fn (Certificado $c) => $c->user->name);

    if ($certificados->isEmpty()) {
        return back()->with('error', 'Este módulo todavía no tiene certificados emitidos.');
    }

    $zipPath = tempnam(sys_get_temp_dir(), 'cert');
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($certificados as $certificado) {
        $nombre = Str::slug($certificado->user->name, '_');
        $zip->addFromString(
            "certificado-{$periodo}-{$certificado->numero}-{$nombre}.pdf",
            $certificado->pdfPeriodo($periodo)
        );
    }

    $zip->close();

    return response()->download($zipPath, "certificados-{$periodo}-".Str::slug($modulo->titulo).'.zip')
        ->deleteFileAfterSend();
})->whereNumber('periodo')->middleware(['auth', 'verified'])->name('modulos.certificados.periodo.zip');

require __DIR__.'/auth.php';
