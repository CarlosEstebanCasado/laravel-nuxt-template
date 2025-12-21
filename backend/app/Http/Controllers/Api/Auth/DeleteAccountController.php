<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeleteAccountController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'confirmation' => ['required', 'string', Rule::in(['DELETE'])],
            // Optional: email/password accounts can provide their current password for extra safety.
            // Social login accounts typically don't know the random password we generate.
            'password' => ['nullable', 'string', 'current_password:web'],
        ], [
            'confirmation.in' => __('Please type DELETE to confirm account deletion.'),
            'password.current_password' => __('The provided password does not match your current password.'),
        ]);

        $user = $request->user();

        // Best-effort: revoke Sanctum personal access tokens if the app uses them.
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        // Logout and invalidate session (stateful Sanctum).
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Finally, delete the user.
        $user->delete();

        return response()->json([
            'message' => 'Account deleted.',
        ], 200);
    }
}


