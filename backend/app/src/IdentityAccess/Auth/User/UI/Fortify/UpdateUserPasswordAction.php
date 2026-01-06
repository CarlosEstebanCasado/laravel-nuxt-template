<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Fortify;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPasswordUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserPasswordUseCase;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use App\Src\Shared\Domain\Service\Translator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPasswordAction implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private readonly UpdateUserPasswordUseCase $updateUserPasswordUseCase,
        private readonly Translator $translator
    ) {}

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => $this->translator->translate('messages.auth.password_mismatch'),
        ])->validateWithBag('updatePassword');

        $this->updateUserPasswordUseCase->execute(new UpdateUserPasswordUseCaseRequest(
            userId: $this->resolveUserId($user),
            password: $input['password'],
        ));

        // We update the password through a repository (different Eloquent instance),
        // so refresh the currently authenticated model before calling logoutOtherDevices().
        $user->refresh();

        // Keep this session, invalidate other sessions/devices.
        // Requires AuthenticateSession middleware in the 'web' group.
        Auth::logoutOtherDevices($input['password']);
    }

    private function resolveUserId(User $user): int
    {
        $userId = $user->getAuthIdentifier();

        if (! is_int($userId) && ! is_string($userId)) {
            throw new \InvalidArgumentException('User identifier must be string or int.');
        }

        if (! is_numeric($userId)) {
            throw new \InvalidArgumentException('User identifier must be numeric.');
        }

        return (int) $userId;
    }
}
