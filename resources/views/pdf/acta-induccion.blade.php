<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 24pt; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1f2937;
            margin: 0;
        }

        .logo {
            text-align: center;
        }

        .logo img {
            height: 40pt;
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
            font-size: 14pt;
            color: #681878;
            letter-spacing: 0.5pt;
            margin: 12pt 0 14pt;
        }

        .datos {
            margin: 0 0 14pt;
            font-size: 9.5pt;
        }

        .datos td {
            padding: 2pt 0;
            vertical-align: top;
        }

        .datos .etiqueta {
            width: 90pt;
            color: #6b7280;
        }

        .datos .valor {
            font-weight: bold;
            color: #681878;
        }

        table.asistencia {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        table.asistencia th {
            background: #fdf2f8;
            color: #681878;
            text-align: left;
            padding: 6pt 8pt;
            border: 0.75pt solid #e9d5ff;
        }

        table.asistencia td {
            padding: 10pt 8pt;
            border: 0.75pt solid #e9d5ff;
            height: 22pt;
        }

        table.asistencia td.nombre {
            font-weight: bold;
            color: #681878;
        }

        table.asistencia td.cedula, table.asistencia td.firma {
            color: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="logo">
        @if (is_file(config('xr_rrhh.empresa_logo')))
            <img src="{{ config('xr_rrhh.empresa_logo') }}" alt="{{ config('xr_rrhh.empresa_nombre') }}">
        @endif
    </div>
    <p class="empresa">{{ config('xr_rrhh.empresa_nombre') }} · Talento Humano</p>

    <h1>ACTA DE ASISTENCIA</h1>

    <table class="datos" width="100%">
        <tr>
            <td class="etiqueta">Módulo:</td>
            <td class="valor">{{ $capacitacion->modulo->titulo }}</td>
        </tr>
        <tr>
            <td class="etiqueta">Fecha:</td>
            <td class="valor">{{ $capacitacion->fecha->format('d/m/Y H:i') }}</td>
        </tr>
        @if ($capacitacion->modalidad)
            <tr>
                <td class="etiqueta">Modalidad:</td>
                <td class="valor">{{ $capacitacion->modalidad }}</td>
            </tr>
        @endif
        @if ($capacitacion->lugar)
            <tr>
                <td class="etiqueta">Lugar:</td>
                <td class="valor">{{ $capacitacion->lugar }}</td>
            </tr>
        @endif
        <tr>
            <td class="etiqueta">Instructor:</td>
            <td class="valor">{{ $capacitacion->instructor->name }}</td>
        </tr>
    </table>

    <table class="asistencia">
        <thead>
            <tr>
                <th width="45%">Nombre y Apellido</th>
                <th width="25%">Cédula</th>
                <th width="30%">Firma</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($asistentes as $asistente)
                <tr>
                    <td class="nombre">{{ $asistente->user->name }}</td>
                    <td class="cedula">&nbsp;</td>
                    <td class="firma">&nbsp;</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
