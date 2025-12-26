<?php

namespace App\Src\Auth\User\Application\UseCase;

use App\Src\Auth\User\Application\Request\UpdateUserProfileUseCaseRequest;
use App\Src\Auth\User\Domain\Repository\UserRepository;
use App\Src\Auth\User\Domain\ValueObject\EmailAddress;
use App\Src\Auth\User\Domain\ValueObject\UserId;

final class UpdateUserProfileUseCase
{
    public function __construct(
        private readonly UserRepository $users
    ) {
    }

    public function execute(UpdateUserProfileUseCaseRequest $request): UpdateUserProfileResult
    {
        $userId = new UserId($request->userId);

        // Step-up auth validation is handled at the UI boundary (Fortify action / FormRequest).
        // Here we only decide whether to reset verification state.
        $resetEmailVerification = $request->isEmailChanging && $request->mustVerifyEmail;

        $this->users->updateProfile(
            id: $userId,
            name: $request->name,
            email: new EmailAddress($request->email),
            resetEmailVerification: $resetEmailVerification,
        );

        return new UpdateUserProfileResult(
            shouldSendEmailVerificationNotification: $resetEmailVerification
        );
    }
}


