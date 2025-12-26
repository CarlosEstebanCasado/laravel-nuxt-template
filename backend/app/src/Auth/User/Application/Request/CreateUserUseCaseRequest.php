<?php

namespace App\Src\Auth\User\Application\Request;

final class CreateUserUseCaseRequest
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}




