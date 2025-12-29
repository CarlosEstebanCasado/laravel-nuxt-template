<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\CreateUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function execute(CreateUserUseCaseRequest $request): UserId
    {
        return $this->userRepository->createPasswordUser(
            name: $request->name,
            email: new EmailAddress($request->email),
            plainPassword: $request->password,
            passwordSetAt: new \DateTimeImmutable(),
        );
    }
}



