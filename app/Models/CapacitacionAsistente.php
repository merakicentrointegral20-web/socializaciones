<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacitacionAsistente extends Model
{
    protected $fillable = [
        'capacitacion_id',
        'user_id',
        'asistio',
        'asistio_at',
    ];

    protected function casts(): array
    {
        return [
            'asistio' => 'boolean',
            'asistio_at' => 'datetime',
        ];
    }

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
