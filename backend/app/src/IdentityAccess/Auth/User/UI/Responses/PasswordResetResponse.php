<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use App\Src\Shared\Domain\Service\Translator;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;

class PasswordResetResponse implements PasswordResetResponseContract
{
    public function __construct(
        protected string $status,
        protected Translator $translator
    ) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'message' => $this->translator->translate($this->status),
        ]);
    }
}
