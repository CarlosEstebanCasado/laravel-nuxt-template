<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\ResetUserPasswordUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class ResetUserPasswordUseCase
{
    public function __construct(
        private readonly UserRepository $users
    ) {
    }

    public function execute(ResetUserPasswordUseCaseRequest $request): void
    {
        // Keep legacy behavior: do not touch password_set_at on reset.
        $this->users->updatePassword(
            id: new UserId($request->userId),
            plainPassword: $request->password,
            passwordSetAt: null,
        );
    }
}




