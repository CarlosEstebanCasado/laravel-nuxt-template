<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use App\Src\Shared\Domain\Service\Translator;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse as FailedPasswordResetResponseContract;
use Laravel\Fortify\Fortify;

class FailedPasswordResetResponse implements FailedPasswordResetResponseContract
{
    public function __construct(
        protected string $status,
        protected Translator $translator
    ) {}

    public function toResponse($request): JsonResponse
    {
        $message = $this->translator->translate($this->status);

        return response()->json([
            'message' => $message,
            'errors' => [
                Fortify::email() => [$message],
            ],
        ], 422);
    }
}
