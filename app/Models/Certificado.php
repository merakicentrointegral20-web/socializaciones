<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
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
        $path = 'certificados/'.$this->id.'.pdf';
        Storage::disk('local')->put($path, $this->renderizarPdf($this->emitido_at));

        $this->update(['pdf_path' => $path]);

        return $path;
    }

    // Versión del certificado para un período de config('xr_rrhh.certificado_periodos').
    // No se guarda en disco: se arma al vuelo en cada descarga.
    public function pdfPeriodo(int $periodo): string
    {
        return $this->renderizarPdf($this->fechaEmisionPeriodo($periodo), [
            'duracion' => config("xr_rrhh.certificado_periodos.{$periodo}.duracion"),
            'periodo' => $periodo,
        ]);
    }

    public function fechaEmisionPeriodo(int $periodo): CarbonInterface
    {
        $rango = config("xr_rrhh.certificado_periodos.{$periodo}.emision");

        if (! $rango) {
            return now();
        }

        $desde = Carbon::parse($rango[0]);
        $dias = (int) $desde->diffInDays(Carbon::parse($rango[1])) + 1;

        return $desde->addDays($this->id % $dias);
    }

    private function renderizarPdf(CarbonInterface $fecha, array $periodo = []): string
    {
        $mesesEs = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
        ];

        return Pdf::loadView('pdf.certificado', [
            'certificado' => $this,
            'nombre' => $this->user->name,
            'cedula' => $this->user->id_card,
            'moduloTitulo' => $this->modulo->titulo,
            'esPresencial' => $this->modulo->esPresencial(),
            'duracion' => $periodo['duracion'] ?? null,
            'periodo' => $periodo['periodo'] ?? null,
            'fechaEmision' => $fecha,
            'fechaTexto' => "a los {$fecha->format('d')} días del mes de {$mesesEs[$fecha->month - 1]} del año {$fecha->format('Y')}",
        ])->setPaper('a4', 'portrait')->output();
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
