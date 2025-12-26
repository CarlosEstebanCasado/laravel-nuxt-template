<?php

namespace App\BoundedContext\Auth\User\Application\UseCase;

use App\BoundedContext\Auth\User\Application\Request\ResetUserPasswordUseCaseRequest;
use App\BoundedContext\Auth\User\Domain\Repository\UserRepository;
use App\BoundedContext\Auth\User\Domain\ValueObject\UserId;
use App\BoundedContext\Shared\Shared\Domain\Service\PasswordHasher;

final class ResetUserPasswordUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher
    ) {
    }

    public function execute(ResetUserPasswordUseCaseRequest $request): void
    {
        // Keep legacy behavior: do not touch password_set_at on reset.
        $this->users->updatePassword(
            id: new UserId($request->userId),
            passwordHash: $this->hasher->hash($request->password),
            passwordSetAt: null,
        );
    }
}




