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
        'umbral_aprobacion',
        'fecha_limite',
        'activo',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'umbral_aprobacion' => 'integer',
            'fecha_limite' => 'date',
            'activo' => 'boolean',
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
}
