<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pregunta extends Model
{
    protected $fillable = [
        'modulo_id',
        'tipo',
        'enunciado',
        'respuesta_correcta',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'respuesta_correcta' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(PreguntaOpcion::class)->orderBy('orden');
    }
}
