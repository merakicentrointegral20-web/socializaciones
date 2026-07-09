<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreguntaOpcion extends Model
{
    // Eloquent pluraliza "PreguntaOpcion" a "pregunta_opcions" (reglas en
    // inglés) -- la tabla real es "pregunta_opciones".
    protected $table = 'pregunta_opciones';

    protected $fillable = [
        'pregunta_id',
        'texto',
        'es_correcta',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'es_correcta' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class);
    }
}
