<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuloVideo extends Model
{
    protected static function booted(): void
    {
        // Sin llave foránea a nivel de MySQL (ver comentario en
        // Modulo::booted()) -- se limpia el progreso de cada colaborador acá.
        static::deleting(function (ModuloVideo $video): void {
            $video->progresos()->delete();
        });
    }

    protected $fillable = [
        'modulo_id',
        'titulo',
        'descripcion',
        'embed_url',
        'orden',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function progresos(): HasMany
    {
        return $this->hasMany(ModuloVideoProgreso::class);
    }
}
