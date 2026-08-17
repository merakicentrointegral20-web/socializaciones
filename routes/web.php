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
