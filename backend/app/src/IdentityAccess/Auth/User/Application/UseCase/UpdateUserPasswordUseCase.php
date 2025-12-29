<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPasswordUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class UpdateUserPasswordUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function execute(UpdateUserPasswordUseCaseRequest $request): void
    {
        $this->userRepository->updatePassword(
            id: new UserId($request->userId),
            plainPassword: $request->password,
            passwordSetAt: new \DateTimeImmutable(),
        );
    }
}



