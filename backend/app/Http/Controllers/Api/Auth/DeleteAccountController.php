<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeleteAccountController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $requiresPassword =
            ($user?->auth_provider === 'password') ||
            (! is_null($user?->password_set_at));

        $validated = $request->validate([
            'confirmation' => ['required', 'string', Rule::in(['DELETE'])],
            // Re-auth (step-up auth) for password-based accounts (and accounts that have set a password).
            // Social login accounts typically don't know the random password we generate.
            'password' => [
                Rule::requiredIf($requiresPassword),
                'string',
                'current_password:web',
            ],
        ], [
            'confirmation.in' => __('Please type DELETE to confirm account deletion.'),
            'password.required' => __('Please confirm your password to continue.'),
            'password.current_password' => __('The provided password does not match your current password.'),
        ]);

        AuditEvent::record(
            user: $user,
            event: 'account_deleted',
            newValues: ['confirmation' => $validated['confirmation']]
        );

        // Best-effort: revoke Sanctum personal access tokens if the app uses them.
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Logout and invalidate session (stateful Sanctum).
        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // Finally, delete the user.
        $user->delete();

        return response()->json([
            'message' => 'Account deleted.',
        ], 200);
    }
}


