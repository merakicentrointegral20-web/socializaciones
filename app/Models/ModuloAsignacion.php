<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuloAsignacion extends Model
{
    // Eloquent pluraliza "Asignacion" a "asignacions" (reglas en inglés) -- la
    // tabla real es "asignaciones" (mismo caso que Capacitacion::$table).
    protected $table = 'modulo_asignaciones';

    protected $fillable = [
        'modulo_id',
        'user_id',
        'assigned_by',
    ];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
