<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Converter\UserResponseConverter;
use App\Src\IdentityAccess\Auth\User\Application\Request\GetCurrentUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Response\GetCurrentUserUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class GetCurrentUserUseCase
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserResponseConverter $converter,
    ) {
    }

    public function execute(GetCurrentUserUseCaseRequest $request): GetCurrentUserUseCaseResponse
    {
        $user = $this->users->get(new UserId($request->userId));

        return $this->converter->toResponse($user);
    }
}

