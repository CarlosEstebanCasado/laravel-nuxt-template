<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAuthEndpoints
{
    /**
     * Apply conservative rate limits to Fortify POST endpoints that are common abuse targets.
     *
     * We do this in middleware (instead of customizing Fortify routes) to keep the SPA setup simple.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('security.throttling.auth.enabled', true)) {
            return $next($request);
        }

        // Fortify prefix is "auth" in this project.
        $path = ltrim($request->path(), '/');

        if ($request->isMethod('POST')) {
            // POST /auth/forgot-password: prevent email spam & user enumeration attempts.
            if ($path === 'auth/forgot-password') {
                $maxAttempts = (int) config('security.throttling.auth.forgot_password.max_attempts', 5);
                $decaySeconds = (int) config('security.throttling.auth.forgot_password.decay_seconds', 60);

                $email = (string) $request->input('email', '');
                $key = 'auth:forgot-password:'.Str::transliterate(Str::lower($email)).'|'.$request->ip();

                if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                    return response()->json([
                        'message' => __('Too many requests. Please try again later.'),
                        'retry_after' => RateLimiter::availableIn($key),
                    ], 429);
                }

                RateLimiter::hit($key, $decaySeconds);
            }

            // POST /auth/reset-password: protect from brute-force attempts.
            if ($path === 'auth/reset-password') {
                $maxAttempts = (int) config('security.throttling.auth.reset_password.max_attempts', 10);
                $decaySeconds = (int) config('security.throttling.auth.reset_password.decay_seconds', 60);

                $key = 'auth:reset-password:'.$request->ip();

                if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                    return response()->json([
                        'message' => __('Too many requests. Please try again later.'),
                        'retry_after' => RateLimiter::availableIn($key),
                    ], 429);
                }

                RateLimiter::hit($key, $decaySeconds);
            }

            // POST /auth/register: avoid automated account creation bursts.
            if ($path === 'auth/register') {
                $maxAttempts = (int) config('security.throttling.auth.register.max_attempts', 10);
                $decaySeconds = (int) config('security.throttling.auth.register.decay_seconds', 60);

                $key = 'auth:register:'.$request->ip();

                if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                    return response()->json([
                        'message' => __('Too many requests. Please try again later.'),
                        'retry_after' => RateLimiter::availableIn($key),
                    ], 429);
                }

                RateLimiter::hit($key, $decaySeconds);
            }
        }

        return $next($request);
    }
}


