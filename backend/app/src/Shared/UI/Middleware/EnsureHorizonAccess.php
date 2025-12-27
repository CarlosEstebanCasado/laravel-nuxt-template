<?php
declare(strict_types=1);

namespace App\Src\Shared\UI\Middleware;

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
        // Only allow access to authenticated users.
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // In local/dev environments, it's usually enough to require authentication.
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        // In non-local environments, require explicit allow-listing.
        $allowedEmails = (array) config('horizon.allowed_emails', []);

        if (empty($allowedEmails) || ! in_array($user->email, $allowedEmails, true)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
