<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function __construct(
        protected string $status
    ) {
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'message' => trans($this->status),
        ]);
    }
}
