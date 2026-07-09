<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColaboradorContacto extends Model
{
    protected $fillable = [
        'user_id',
        'telefono',
        'cargo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Número en formato internacional para links wa.me (sin "+", sin
     * espacios ni guiones). Asume Ecuador si viene en formato local
     * (empieza con 0): "0991234567" -> "593991234567".
     */
    public function telefonoWhatsapp(): ?string
    {
        if (! $this->telefono) {
            return null;
        }

        $digitos = preg_replace('/\D/', '', $this->telefono);

        if (str_starts_with($digitos, '0')) {
            $digitos = '593'.substr($digitos, 1);
        }

        return $digitos ?: null;
    }
}
