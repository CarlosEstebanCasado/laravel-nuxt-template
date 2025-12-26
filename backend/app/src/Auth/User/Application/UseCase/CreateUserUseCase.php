<?php

namespace App\BoundedContext\Auth\User\Application\UseCase;

use App\BoundedContext\Auth\User\Application\Request\CreateUserUseCaseRequest;
use App\BoundedContext\Auth\User\Domain\Repository\UserRepository;
use App\BoundedContext\Auth\User\Domain\ValueObject\EmailAddress;
use App\BoundedContext\Auth\User\Domain\ValueObject\UserId;
use App\BoundedContext\Shared\Shared\Domain\Service\PasswordHasher;

final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $hasher
    ) {
    }

    public function execute(CreateUserUseCaseRequest $request): UserId
    {
        $passwordHash = $this->hasher->hash($request->password);

        return $this->users->createPasswordUser(
            name: $request->name,
            email: new EmailAddress($request->email),
            passwordHash: $passwordHash,
            passwordSetAt: new \DateTimeImmutable(),
        );
    }
}




