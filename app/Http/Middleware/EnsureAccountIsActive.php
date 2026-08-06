<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * `users.is_active` es columna de xuri-apex (ver database/mysql-app-user.sql,
 * SELECT de tabla completa sobre `users`) -- si esa app desactiva a alguien,
 * el login (LoginForm::authenticate) ya lo bloquea, pero no cierra una
 * sesión de xr-rrhh que haya quedado abierta desde antes de la
 * desactivación. Este middleware cubre justo ese caso: global en el grupo
 * `web`, así que corre en cada request sin tener que agregarlo ruta por
 * ruta, y no hace nada si no hay sesión iniciada.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'form.email' => __('Tu cuenta fue desactivada.'),
            ]);
        }

        return $next($request);
    }
}
