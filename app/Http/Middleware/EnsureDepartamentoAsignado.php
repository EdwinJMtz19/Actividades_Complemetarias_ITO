<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un coordinador sin departamento asignado no puede operar: su alcance
 * depende de ese dato. El administrador y los demás roles no se ven afectados.
 */
class EnsureDepartamentoAsignado
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && $usuario->esCoordinador() && ! $usuario->esAdministrador()
            && $usuario->departamentoId() === null) {
            abort(403, 'Tu cuenta de coordinador aún no tiene un departamento asignado. Contacta al administrador.');
        }

        return $next($request);
    }
}
