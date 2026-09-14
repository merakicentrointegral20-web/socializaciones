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
            color: #143363;
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
            color: #143363;
        }

        table.asistencia {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        table.asistencia th {
            background: #f3f4f6;
            color: #374151;
            text-align: left;
            padding: 6pt 8pt;
            border: 0.75pt solid #d1d5db;
        }

        table.asistencia td {
            padding: 10pt 8pt;
            border: 0.75pt solid #d1d5db;
            height: 22pt;
        }

        table.asistencia td.nombre {
            font-weight: bold;
            color: #143363;
        }

        table.asistencia td.cedula {
            color: #374151;
        }

        table.asistencia td.firma {
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

    <h1>ACTA DE ASIGNACIÓN DE MÓDULO</h1>

    <table class="datos" width="100%">
        <tr>
            <td class="etiqueta">Módulo:</td>
            <td class="valor">{{ $modulo->titulo }}</td>
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
            @foreach ($colaboradores as $colaborador)
                <tr>
                    <td class="nombre">{{ $colaborador->name }}</td>
                    <td class="cedula">{{ $colaborador->id_card ?? '—' }}</td>
                    <td class="firma">&nbsp;</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
