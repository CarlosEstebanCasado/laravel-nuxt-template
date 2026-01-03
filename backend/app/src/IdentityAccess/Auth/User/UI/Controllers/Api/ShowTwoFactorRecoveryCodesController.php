<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Controllers\Api;

use App\Src\Shared\UI\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;

class ShowTwoFactorRecoveryCodesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->requireUser($request);

        $request->validate([
            'password' => ['required', 'string', 'current_password:web'],
        ]);

        if (! $user->two_factor_secret || ! $user->two_factor_recovery_codes) {
            return response()->json([]);
        }

        $codes = json_decode(Fortify::currentEncrypter()->decrypt(
            $user->two_factor_recovery_codes
        ), true);

        return response()->json($codes);
    }
}
