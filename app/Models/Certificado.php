<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Certificado extends Model
{
    protected $fillable = [
        'user_id',
        'modulo_id',
        'evaluacion_intento_id',
        'codigo_verificacion',
        'numero',
        'emitido_at',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'emitido_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Certificado $certificado): void {
            $certificado->codigo_verificacion ??= (string) Str::uuid();

            // Correlativo simple por año, ej. "001-2026". No pretende ser a
            // prueba de condiciones de carrera (no hay lock), pero acá el
            // volumen de certificados emitidos a la vez es mínimo.
            if (! $certificado->numero) {
                $anio = now()->year;
                $siguiente = static::whereYear('created_at', $anio)->count() + 1;
                $certificado->numero = sprintf('%03d-%d', $siguiente, $anio);
            }
        });
    }

    public function generarPdf(): string
    {
        $mesesEs = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
        ];

        $fecha = $this->emitido_at;

        $pdf = Pdf::loadView('pdf.certificado', [
            'certificado' => $this,
            'nombre' => $this->user->name,
            'cedula' => $this->user->id_card,
            'moduloTitulo' => $this->modulo->titulo,
            'esPresencial' => $this->modulo->esPresencial(),
            'fechaTexto' => "a los {$fecha->format('d')} días del mes de {$mesesEs[$fecha->month - 1]} del año {$fecha->format('Y')}",
        ])->setPaper('a4', 'portrait');

        $path = 'certificados/'.$this->id.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        $this->update(['pdf_path' => $path]);

        return $path;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function evaluacionIntento(): BelongsTo
    {
        return $this->belongsTo(EvaluacionIntento::class);
    }
}
