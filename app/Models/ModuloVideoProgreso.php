<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuloVideoProgreso extends Model
{
    protected $fillable = [
        'modulo_video_id',
        'user_id',
        'completado_at',
    ];

    protected function casts(): array
    {
        return [
            'completado_at' => 'datetime',
        ];
    }

    public function moduloVideo(): BelongsTo
    {
        return $this->belongsTo(ModuloVideo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
