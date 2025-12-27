<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Security\Reauth\UI\Middleware;

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
        if (! $this->boolFromConfig('security.throttling.auth.enabled', true)) {
            return $next($request);
        }

        // Fortify prefix is "auth" in this project.
        $path = ltrim($request->path(), '/');

        if ($request->isMethod('POST')) {
            // POST /auth/forgot-password: prevent email spam & user enumeration attempts.
            if ($path === 'auth/forgot-password') {
                $maxAttempts = $this->intFromConfig('security.throttling.auth.forgot_password.max_attempts', 5);
                $decaySeconds = $this->intFromConfig('security.throttling.auth.forgot_password.decay_seconds', 60);

                $emailInput = $request->input('email', '');
                $email = is_string($emailInput) ? $emailInput : '';
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
                $maxAttempts = $this->intFromConfig('security.throttling.auth.reset_password.max_attempts', 10);
                $decaySeconds = $this->intFromConfig('security.throttling.auth.reset_password.decay_seconds', 60);

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
                $maxAttempts = $this->intFromConfig('security.throttling.auth.register.max_attempts', 10);
                $decaySeconds = $this->intFromConfig('security.throttling.auth.register.decay_seconds', 60);

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

    private function boolFromConfig(string $key, bool $default): bool
    {
        $value = config($key);

        return is_bool($value) ? $value : $default;
    }

    private function intFromConfig(string $key, int $default): int
    {
        $value = config($key);

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }
}

