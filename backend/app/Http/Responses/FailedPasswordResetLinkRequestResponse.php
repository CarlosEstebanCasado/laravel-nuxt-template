<?php

namespace App\Http\Responses;

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
        $message = trans($this->status);

        return response()->json([
            'message' => $message,
            'errors' => [
                Fortify::email() => [$message],
            ],
        ], 422);
    }
}
