<?php

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class SuccessfulPasswordResetLinkRequestResponse implements SuccessfulPasswordResetLinkRequestResponseContract
{
    public function __construct(
        protected string $status
    ) {
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            // Avoid account enumeration: do not reveal whether the email exists.
            'message' => __('If an account exists for that email, you will receive a password reset link.'),
        ]);
    }
}
