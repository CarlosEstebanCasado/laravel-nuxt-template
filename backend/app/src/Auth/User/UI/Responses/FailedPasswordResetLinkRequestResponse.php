<?php

namespace App\Src\Auth\User\UI\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Laravel\Fortify\Fortify;

class FailedPasswordResetLinkRequestResponse implements FailedPasswordResetLinkRequestResponseContract
{
    public function __construct(
        protected string $status
    ) {
    }

    public function toResponse($request): JsonResponse
    {
        // Avoid account enumeration: do not reveal whether the email exists.
        return response()->json([
            'message' => __('If an account exists for that email, you will receive a password reset link.'),
        ]);
    }
}
