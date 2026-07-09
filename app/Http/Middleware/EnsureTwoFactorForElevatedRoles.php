<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Punto de enganche para 2FA obligatorio en cuentas con capacidades
 * elevadas: el rol de instructor (crea módulos/gestiona capacitaciones) o
 * el permiso de gestión de acceso (activa cuentas para el resto de la
 * nómina).
 *
 * Todavía no hay una implementación de 2FA (TOTP) en el proyecto, así que
 * por ahora este middleware deja pasar toda solicitud sin bloquear a
 * nadie. Ya está registrado como alias `elevated.2fa` y aplicado a las
 * rutas autenticadas (ver routes/web.php), para que activar la
 * verificación real más adelante sea solo cuestión de completar la
 * condición de abajo, sin tener que volver a cablear rutas.
 */
class EnsureTwoFactorForElevatedRoles
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isElevated = $user && (
            $user->hasAnyRole(config('xr_rrhh.elevated_roles'))
            || $user->hasAnyPermission(config('xr_rrhh.elevated_permissions'))
        );

        if ($isElevated) {
            // TODO: cuando exista 2FA, verificar aquí si el usuario ya
            // confirmó el segundo factor en esta sesión y, si no,
            // redirigir al challenge en lugar de continuar.
        }

        return $next($request);
    }
}
