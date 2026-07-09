<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ModuloArchivo extends Model
{
    protected $fillable = [
        'modulo_id',
        'nombre_original',
        'disk_path',
        'tipo',
        'tamano',
        'orden',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tamano' => 'integer',
            'orden' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (ModuloArchivo $archivo): void {
            Storage::disk('local')->delete($archivo->disk_path);
        });
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function esImagen(): bool
    {
        return in_array($this->tipo, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }

    public function tamanoLegible(): string
    {
        if (! $this->tamano) {
            return '';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $tamano = $this->tamano;
        $i = 0;

        while ($tamano >= 1024 && $i < count($unidades) - 1) {
            $tamano /= 1024;
            $i++;
        }

        return round($tamano, 1).' '.$unidades[$i];
    }
}
