<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\UseCase;

use App\Src\IdentityAccess\Auth\User\Application\Request\GetUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPreferencesUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\Response\GetUserPreferencesUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;
use App\Src\IdentityAccess\Auth\User\Domain\Repository\UserPreferencesRepository;
use App\Src\IdentityAccess\Auth\User\Domain\Service\UserPreferencesUpdater;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Locale;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\NeutralColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\PrimaryColor;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Theme;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\Timezone;
use App\Src\IdentityAccess\Auth\User\Domain\ValueObject\UserId;
use App\Src\Shared\Domain\Service\ConfigProvider;

final class UpdateUserPreferencesUseCase
{
    public function __construct(
        private readonly UserPreferencesRepository $userPreferencesRepository,
        private readonly GetUserPreferencesUseCase $getUserPreferencesUseCase,
        private readonly UserPreferencesUpdater $userPreferencesUpdater,
        private readonly ConfigProvider $configProvider
    ) {
    }

    public function execute(UpdateUserPreferencesUseCaseRequest $request): GetUserPreferencesUseCaseResponse
    {
        $userId = new UserId($request->userId);

        $preferences = $this->userPreferencesRepository->find($userId)
            ?? UserPreferences::default($userId, $this->configProvider);

        $updatedPreferences = $this->userPreferencesUpdater->update(
            $preferences,
            $request->locale !== null ? new Locale($request->locale) : null,
            $request->theme !== null ? new Theme($request->theme) : null,
            $request->primaryColor !== null ? new PrimaryColor($request->primaryColor) : null,
            $request->neutralColor !== null ? new NeutralColor($request->neutralColor) : null,
            $request->timezone !== null ? new Timezone($request->timezone) : null
        );

        $this->userPreferencesRepository->save($updatedPreferences);

        return $this->getUserPreferencesUseCase->execute(
            new GetUserPreferencesUseCaseRequest(
                userId: $request->userId
            )
        );
    }
}
