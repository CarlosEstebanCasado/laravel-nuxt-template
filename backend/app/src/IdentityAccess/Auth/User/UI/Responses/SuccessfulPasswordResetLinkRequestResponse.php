<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Responses;

use App\Src\Shared\Domain\Service\Translator;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class SuccessfulPasswordResetLinkRequestResponse implements SuccessfulPasswordResetLinkRequestResponseContract
{
    public function __construct(
        protected string $status,
        protected Translator $translator
    ) {}

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            // Avoid account enumeration: do not reveal whether the email exists.
            'message' => $this->translator->translate('messages.auth.password_reset_link_sent'),
        ]);
    }
}
