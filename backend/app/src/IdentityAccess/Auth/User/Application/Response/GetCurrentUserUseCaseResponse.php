<?php

namespace App\Src\IdentityAccess\Auth\User\Application\Response;

final class GetCurrentUserUseCaseResponse
{
    public function __construct(
        public UserResponseItem $data
    ) {
    }
}

