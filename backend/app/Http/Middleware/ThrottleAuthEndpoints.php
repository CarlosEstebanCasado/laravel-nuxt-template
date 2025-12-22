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
        // Fortify prefix is "auth" in this project.
        $path = ltrim($request->path(), '/');

        if ($request->isMethod('POST')) {
            // POST /auth/forgot-password: prevent email spam & user enumeration attempts.
            if ($path === 'auth/forgot-password') {
                $email = (string) $request->input('email', '');
                $key = 'auth:forgot-password:'.Str::transliterate(Str::lower($email)).'|'.$request->ip();

                if (RateLimiter::tooManyAttempts($key, 5)) {
                    return response()->json([
                        'message' => __('Too many requests. Please try again later.'),
                    ], 429);
                }

                RateLimiter::hit($key, 60);
            }

            // POST /auth/reset-password: protect from brute-force attempts.
            if ($path === 'auth/reset-password') {
                $key = 'auth:reset-password:'.$request->ip();

                if (RateLimiter::tooManyAttempts($key, 10)) {
                    return response()->json([
                        'message' => __('Too many requests. Please try again later.'),
                    ], 429);
                }

                RateLimiter::hit($key, 60);
            }

            // POST /auth/register: avoid automated account creation bursts.
            if ($path === 'auth/register') {
                $key = 'auth:register:'.$request->ip();

                if (RateLimiter::tooManyAttempts($key, 10)) {
                    return response()->json([
                        'message' => __('Too many requests. Please try again later.'),
                    ], 429);
                }

                RateLimiter::hit($key, 60);
            }
        }

        return $next($request);
    }
}


