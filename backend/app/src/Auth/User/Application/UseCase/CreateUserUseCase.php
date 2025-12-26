<?php

namespace App\Src\Auth\User\Application\UseCase;

use App\Src\Auth\User\Application\Request\CreateUserUseCaseRequest;
use App\Src\Auth\User\Domain\Repository\UserRepository;
use App\Src\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\Auth\User\Domain\ValueObject\UserId;

final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepository $users
    ) {
    }

    public function execute(CreateUserUseCaseRequest $request): UserId
    {
        return $this->users->createPasswordUser(
            name: $request->name,
            email: new EmailAddress($request->email),
            plainPassword: $request->password,
            passwordSetAt: new \DateTimeImmutable(),
        );
    }
}




