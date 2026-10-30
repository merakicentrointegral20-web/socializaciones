<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rol de instructor
    |--------------------------------------------------------------------------
    |
    | Rol único (tabla `roles` de Spatie, compartida con xuri-apex) para
    | quienes pueden crear módulos y gestionar capacitaciones. La categoría
    | del módulo (RRHH, médico ocupacional, seguridad industrial, ...) es un
    | dato del módulo en sí, no un rol distinto por persona.
    |
    */

    'instructor_role' => 'instructor',

    /*
    |--------------------------------------------------------------------------
    | Rol de solo supervisión
    |--------------------------------------------------------------------------
    |
    | Cuentas que entran solo a supervisar (jefaturas, auditoría, etc.) y no
    | toman los módulos: no salen en las actas, en el seguimiento ni en los
    | conteos de pendientes. Se activa desde la pantalla de Personal.
    |
    */

    'supervision_role' => 'supervision',

    /*
    |--------------------------------------------------------------------------
    | Permiso de gestión de acceso
    |--------------------------------------------------------------------------
    |
    | Quien puede activar/gestionar el acceso al portal para el resto de la
    | nómina. Se otorga directamente a la(s) persona(s) encargadas (no a
    | través de un rol), porque en la práctica es una sola persona (RRHH).
    |
    */

    'manage_access_permission' => 'colaboradores.gestionar',

    /*
    |--------------------------------------------------------------------------
    | Roles/permisos que exigen verificación en dos pasos
    |--------------------------------------------------------------------------
    |
    | Cualquiera de estas capacidades activa el enganche de 2FA (ver
    | App\Http\Middleware\EnsureTwoFactorForElevatedRoles).
    |
    */

    'elevated_roles' => [
        'instructor',
    ],

    'elevated_permissions' => [
        'colaboradores.gestionar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Datos institucionales para el certificado en PDF
    |--------------------------------------------------------------------------
    */

    'empresa_nombre' => env('EMPRESA_NOMBRE', 'MERAKI'),

    'empresa_ciudad' => env('EMPRESA_CIUDAD', 'Guayaquil'),

    'empresa_logo' => env('EMPRESA_LOGO', public_path('img/logo.png')),

    'empresa_logo_web' => env('EMPRESA_LOGO_WEB', 'img/logo.png'),

    'certificado_firma' => env('CERTIFICADO_FIRMA', public_path('img/firma.png')),

    'certificado_firma_nombre' => env('CERTIFICADO_FIRMA_NOMBRE', 'Dirección de Talento Humano'),

    'certificado_firma_cargo' => env('CERTIFICADO_FIRMA_CARGO', 'Talento Humano'),

    /*
    |--------------------------------------------------------------------------
    | Certificados por período
    |--------------------------------------------------------------------------
    |
    | Versiones extra del certificado (con Duración y Período) que se
    | descargan en ZIP desde Seguimiento. `emision` null = fecha del día de
    | la descarga; un rango [desde, hasta] = una fecha dentro de ese rango,
    | fija por certificado para que no todos salgan con el mismo día.
    |
    */

    'certificado_periodos' => [
        2025 => ['duracion' => '10 horas', 'emision' => null],
        2026 => ['duracion' => '10 horas', 'emision' => ['2026-12-14', '2026-12-18']],
    ],

];
