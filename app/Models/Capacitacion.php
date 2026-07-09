<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Capacitacion extends Model
{
    // Eloquent pluraliza "Capacitacion" a "capacitacions" (reglas en
    // inglés) -- la tabla real es "capacitaciones".
    protected $table = 'capacitaciones';

    protected $fillable = [
        'modulo_id',
        'fecha',
        'modalidad',
        'lugar',
        'acta_path',
        'instructor_user_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
        ];
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_user_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function asistentes(): HasMany
    {
        return $this->hasMany(CapacitacionAsistente::class);
    }

    public function evaluacionIntentos(): HasMany
    {
        return $this->hasMany(EvaluacionIntento::class);
    }
}
