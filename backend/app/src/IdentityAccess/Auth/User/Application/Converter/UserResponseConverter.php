<?php

namespace App\Src\IdentityAccess\Auth\User\Application\Converter;

use App\Src\IdentityAccess\Auth\User\Application\Response\GetCurrentUserUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\User;

final class UserResponseConverter
{
    public function __construct(
        private readonly UserResponseItemConverter $itemConverter
    ) {
    }

    public function toResponse(User $user): GetCurrentUserUseCaseResponse
    {
        return new GetCurrentUserUseCaseResponse(
            $this->itemConverter->toResponse($user)
        );
    }
}

