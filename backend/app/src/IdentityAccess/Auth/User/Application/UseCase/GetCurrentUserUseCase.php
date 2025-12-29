<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Converter\UserResponseConverter;
use App\Src\IdentityAccess\Auth\User\Application\Request\GetCurrentUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Response\GetCurrentUserUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserRepository;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;

final class GetCurrentUserUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPreferencesRepository $userPreferencesRepository,
        private readonly UserResponseConverter $userResponseConverter,
    ) {
    }

    public function execute(GetCurrentUserUseCaseRequest $request): GetCurrentUserUseCaseResponse
    {
        $userId = new UserId($request->userId);
        $user = $this->userRepository->get($userId);
        $preferences = $this->userPreferencesRepository->find($userId);

        return $this->userResponseConverter->toResponse($user, $preferences);
    }
}
