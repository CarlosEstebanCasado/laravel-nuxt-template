<?php

namespace App\Src\Auth\User\Application\UseCase;

use App\Src\Auth\User\Application\Request\UpdateUserPasswordUseCaseRequest;
use App\Src\Auth\User\Domain\Repository\UserRepository;
use App\Src\Auth\User\Domain\ValueObject\UserId;
use App\Src\Shared\Shared\Domain\Service\PasswordHasher;

final class UpdateUserPasswordUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher
    ) {
    }

    public function execute(UpdateUserPasswordUseCaseRequest $request): void
    {
        $this->users->updatePassword(
            id: new UserId($request->userId),
            passwordHash: $this->hasher->hash($request->password),
            passwordSetAt: new \DateTimeImmutable(),
        );
    }
}




