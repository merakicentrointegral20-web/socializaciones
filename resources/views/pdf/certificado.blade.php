<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            margin: 0;
        }

        .marco {
            border: 2pt solid #1a4c9a;
            padding: 16pt;
            margin: 12pt;
        }

        .marco-interno {
            border: 0.75pt solid #b3cdef;
            padding: 22pt 34pt;
        }

        .logo {
            text-align: center;
        }

        .logo img {
            height: 48pt;
        }

        .empresa {
            text-align: center;
            font-size: 8.5pt;
            letter-spacing: 1pt;
            color: #6b7280;
            margin-top: 3pt;
            text-transform: uppercase;
        }

        h1 {
            text-align: center;
            font-size: 16pt;
            color: #143363;
            letter-spacing: 0.5pt;
            margin: 12pt 0 2pt;
        }

        .numero {
            text-align: center;
            font-size: 9pt;
            color: #2c65b8;
            margin-bottom: 12pt;
        }

        .intro, .cierre {
            font-size: 9.5pt;
            line-height: 1.45;
            text-align: justify;
        }

        .datos {
            margin: 10pt 0;
            font-size: 10pt;
        }

        .datos td {
            padding: 2pt 0;
            vertical-align: top;
        }

        .datos .etiqueta {
            width: 140pt;
            color: #6b7280;
        }

        .datos .valor {
            font-weight: bold;
            color: #143363;
        }

        ol {
            font-size: 9.5pt;
            line-height: 1.4;
            text-align: justify;
            padding-left: 15pt;
            margin: 8pt 0 12pt;
        }

        ol li {
            margin-bottom: 5pt;
        }

        ol b {
            color: #143363;
        }

        .firma {
            margin-top: 34pt;
            text-align: center;
        }

        .firma .firma-img {
            height: 42pt;
            margin-bottom: -8pt;
        }

        .firma .linea {
            width: 220pt;
            margin: 0 auto;
            border-top: 0.75pt solid #1f2937;
            padding-top: 4pt;
            font-size: 9.5pt;
            font-weight: bold;
            color: #143363;
        }

        .firma .cargo {
            font-size: 8.5pt;
            color: #6b7280;
        }

        .pie {
            margin-top: 18pt;
            font-size: 8.5pt;
            color: #4b5563;
            border-top: 0.5pt solid #e5e7eb;
            padding-top: 8pt;
        }

        .codigo {
            font-size: 7.5pt;
            color: #9ca3af;
            margin-top: 3pt;
        }
    </style>
</head>
<body>
    <div class="marco">
        <div class="marco-interno">
            <div class="logo">
                @if (is_file(config('xr_rrhh.empresa_logo')))
                    <img src="{{ config('xr_rrhh.empresa_logo') }}" alt="{{ config('xr_rrhh.empresa_nombre') }}">
                @endif
            </div>
            <p class="empresa">{{ config('xr_rrhh.empresa_nombre') }} · Talento Humano</p>

            <h1>CERTIFICADO DE APROBACIÓN DE INDUCCIÓN Y EVALUACIONES</h1>
            <p class="numero">No. {{ $certificado->numero }}</p>

            <p class="intro">
                Por medio del presente documento, el Departamento de Talento Humano de {{ config('xr_rrhh.empresa_nombre') }} certifica que:
            </p>

            <table class="datos" width="100%">
                <tr>
                    <td class="etiqueta">Nombre:</td>
                    <td class="valor">{{ $nombre }}</td>
                </tr>
                @if ($cedula)
                    <tr>
                        <td class="etiqueta">Cédula de Identidad:</td>
                        <td class="valor">{{ $cedula }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="etiqueta">Socialización:</td>
                    <td class="valor">{{ $moduloTitulo }}</td>
                </tr>
            </table>

            <p class="intro">
                Ha cumplido rigurosamente con todas las etapas obligatorias del proceso de inducción de la empresa, detalladas a continuación:
            </p>

            <ol>
                <li><b>MATERIAL PRE-INDUCCIÓN:</b> Realizó la lectura, revisión y análisis obligatorio de la guía técnica previa.</li>
                @if ($esPresencial)
                    <li><b>SOCIALIZACIÓN PRESENCIAL:</b> Asistió de forma obligatoria y participativa a la jornada de socialización presencial enfocada específicamente en el tema: {{ $moduloTitulo }}.</li>
                @else
                    <li><b>SOCIALIZACIÓN VIRTUAL:</b> Completó de forma obligatoria y secuencial la socialización virtual (contenido y videos) enfocada específicamente en el tema: {{ $moduloTitulo }}.</li>
                @endif
                <li><b>EVALUACIÓN DE CONOCIMIENTOS:</b> Rindió satisfactoriamente los test correspondientes al tema socializado, aprobando los mismos con una calificación sobresaliente.</li>
            </ol>

            <p class="cierre">
                Para constancia de lo actuado y cumplimiento de las normativas de la organización, se extiende el presente
                certificado en la ciudad de {{ config('xr_rrhh.empresa_ciudad') }}, {{ $fechaTexto }}.
            </p>

            <div class="firma">
                @if (is_file(config('xr_rrhh.certificado_firma')))
                    <img class="firma-img" src="{{ config('xr_rrhh.certificado_firma') }}" alt="Firma">
                @endif
                <div class="linea">&nbsp;</div>
                <div class="cargo">Lcdo. Antonio Ordoñez</div>
                <div class="cargo">Talento Humano</div>

            </div>

            <div class="pie">
                <div>Emitido el {{ $certificado->emitido_at->format('d/m/Y') }}</div>
                <div class="codigo">Código de verificación: {{ $certificado->codigo_verificacion }}</div>
            </div>
        </div>
    </div>
</body>
</html>
