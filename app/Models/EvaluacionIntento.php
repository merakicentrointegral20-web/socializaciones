<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EvaluacionIntento extends Model
{
    protected $fillable = [
        'user_id',
        'modulo_id',
        'capacitacion_id',
        'score',
        'total_preguntas',
        'aprobado',
        'intento_numero',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'total_preguntas' => 'integer',
            'aprobado' => 'boolean',
            'intento_numero' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class);
    }

    public function certificado(): HasOne
    {
        return $this->hasOne(Certificado::class);
    }
}
