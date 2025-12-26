<?php

namespace App\Src\Auth\User\Application\Request;

final class OAuthCallbackUseCaseRequest
{
    public function __construct(
        public readonly string $provider,
        public readonly string $email,
        public readonly ?string $name,
        public readonly ?string $nickname,
    ) {
    }
}


