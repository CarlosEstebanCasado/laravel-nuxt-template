<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHorizonAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo permitir acceso a usuarios autenticados
        if (!auth()->check()) {
            // Redirigir al login en lugar de mostrar error 403
            return redirect()->route('login');
        }

        // Solo permitir acceso a administradores o usuarios específicos
        // Puedes ajustar esta lógica según tus necesidades
        $user = auth()->user();
        
        // Opción 1: Solo administradores (si tienes un campo is_admin)
        // if (!$user->is_admin) {
        //     abort(403, 'Acceso denegado. Solo administradores pueden acceder a Horizon.');
        // }

        // Opción 2: Solo usuarios con email específico (más seguro para desarrollo)
        $allowedEmails = [
            'admin@project.dev',
            'carlos@example.com', // Cambia por tu email
        ];
        
        if (!in_array($user->email, $allowedEmails)) {
            abort(403, 'Acceso denegado. No tienes permisos para acceder a Horizon.');
        }

        return $next($request);
    }
}
