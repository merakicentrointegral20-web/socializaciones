<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Modulo extends Model
{
    protected $fillable = [
        'titulo',
        'contenido',
        'categoria',
        'tipo_induccion',
        'umbral_aprobacion',
        'fecha_limite',
        'activo',
        'asignado_a_todos',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'umbral_aprobacion' => 'integer',
            'fecha_limite' => 'date',
            'activo' => 'boolean',
            'asignado_a_todos' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Modulo $modulo): void {
            // La FK de modulo_archivos borra sus filas en cascada a nivel
            // de MySQL, lo que NO dispara el evento "deleting" de
            // ModuloArchivo (que borraría el archivo físico). Se borra
            // aquí todo el directorio del módulo para no dejar huérfanos.
            Storage::disk('local')->deleteDirectory('modulos/'.$modulo->id);

            // modulo_videos y modulo_asignaciones NO tienen llave foránea a
            // nivel de MySQL (el usuario de producción no tiene el
            // privilegio REFERENCES y el hosting no lo concede -- ver
            // database/mysql-app-user.sql), así que la cascada se hace acá.
            // ->each()->delete() (no un delete() masivo) para que también
            // dispare el evento "deleting" de ModuloVideo y limpie sus
            // progresos.
            $modulo->videos()->get()->each->delete();
            $modulo->asignaciones()->delete();
        });
    }

    // El editor de texto (Quill) deja marcado tipo "<p><br></p>" cuando el
    // usuario no escribió nada -- no es una cadena vacía, así que hay que
    // limpiarlo a mano para que el contenido se guarde como null de verdad.
    public static function limpiarContenido(?string $html): ?string
    {
        if ($html === null || trim(strip_tags($html)) === '') {
            return null;
        }

        return $html;
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function capacitaciones(): HasMany
    {
        return $this->hasMany(Capacitacion::class);
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class)->orderBy('orden');
    }

    public function evaluacionIntentos(): HasMany
    {
        return $this->hasMany(EvaluacionIntento::class);
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class);
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(ModuloLectura::class);
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ModuloArchivo::class)->orderBy('orden');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ModuloVideo::class)->orderBy('orden');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(ModuloAsignacion::class);
    }

    public function visiblePara(User $user): bool
    {
        return $this->asignado_a_todos
            || $this->asignaciones()->where('user_id', $user->id)->exists();
    }

    public function esPresencial(): bool
    {
        return $this->tipo_induccion === 'presencial';
    }

    // Un módulo sin videos no bloquea la evaluación por este lado -- el
    // desbloqueo secuencial estricto vive en marcarVideoCompletado() y acá solo
    // se verifica que ya no quede ninguno pendiente.
    public function todosLosVideosCompletados(int $userId): bool
    {
        $idsVideos = $this->videos()->pluck('id');

        if ($idsVideos->isEmpty()) {
            return true;
        }

        $completados = ModuloVideoProgreso::whereIn('modulo_video_id', $idsVideos)
            ->where('user_id', $userId)
            ->whereNotNull('completado_at')
            ->count();

        return $completados >= $idsVideos->count();
    }
}
