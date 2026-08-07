<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuloLectura extends Model
{
    protected $fillable = [
        'modulo_id',
        'user_id',
        'documento_abierto_at',
        'leido_at',
    ];

    protected function casts(): array
    {
        return [
            'documento_abierto_at' => 'datetime',
            'leido_at' => 'datetime',
        ];
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
