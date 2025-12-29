<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\Application\Converter;

use App\Src\IdentityAccess\Auth\User\Application\Response\GetCurrentUserUseCaseResponse;
use App\Src\IdentityAccess\Auth\User\Application\Response\UserPreferencesResponseItem;
use App\Src\IdentityAccess\Auth\User\Application\Response\UserResponseItem;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\User;
use App\Src\IdentityAccess\Auth\User\Domain\Entity\UserPreferences;

final class UserResponseConverter
{
    public function __construct(
        private readonly UserResponseItemConverter $userResponseItemConverter
    ) {
    }

    public function toResponse(User $user, ?UserPreferences $preferences = null): GetCurrentUserUseCaseResponse
    {
        $response = $this->userResponseItemConverter->toResponse($user);

        if ($preferences !== null) {
            $response = new UserResponseItem(
                id: $response->id,
                name: $response->name,
                email: $response->email,
                email_verified_at: $response->email_verified_at,
                auth_provider: $response->auth_provider,
                password_set_at: $response->password_set_at,
                created_at: $response->created_at,
                updated_at: $response->updated_at,
                preferences: new UserPreferencesResponseItem(
                    locale: $preferences->locale(),
                    theme: $preferences->theme(),
                    primary_color: $preferences->primaryColor(),
                    neutral_color: $preferences->neutralColor(),
                ),
            );
        }

        return new GetCurrentUserUseCaseResponse($response);
    }
}
