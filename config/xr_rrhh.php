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

    'empresa_nombre' => 'XURI S.A.',

    'empresa_ciudad' => 'La Troncal',

    'empresa_logo' => public_path('img/impxurisa-04.svg'),

    'certificado_firma' => public_path('img/firma-antonio-ordonez.png'),

];
