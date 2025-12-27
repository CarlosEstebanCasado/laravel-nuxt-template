<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Fortify;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserPasswordUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserPasswordUseCase;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPasswordAction implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private readonly UpdateUserPasswordUseCase $useCase
    ) {
    }

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
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $this->useCase->execute(new UpdateUserPasswordUseCaseRequest(
            userId: (int) $user->getAuthIdentifier(),
            password: $input['password'],
        ));

        // We update the password through a repository (different Eloquent instance),
        // so refresh the currently authenticated model before calling logoutOtherDevices().
        $user->refresh();

        // Keep this session, invalidate other sessions/devices.
        // Requires AuthenticateSession middleware in the 'web' group.
        Auth::logoutOtherDevices($input['password']);
    }
}
