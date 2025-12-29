<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Fortify;

use App\Src\IdentityAccess\Auth\User\Application\Request\UpdateUserProfileUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\UpdateUserProfileUseCase;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformationAction implements UpdatesUserProfileInformation
{
    public function __construct(
        private readonly UpdateUserProfileUseCase $updateUserProfileUseCase
    ) {
    }

    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        $isEmailChanging = isset($input['email']) && $input['email'] !== $user->email;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ];

        // Step-up auth: require current password when changing email for password-based accounts.
        if ($isEmailChanging && (($user->auth_provider === 'password') || (! is_null($user->password_set_at)))) {
            $rules['current_password'] = ['required', 'string', 'current_password:web'];
        }

        Validator::make($input, $rules, [
            'current_password.required' => __('Please confirm your password to change email.'),
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updateProfileInformation');

        $result = $this->updateUserProfileUseCase->execute(
            new UpdateUserProfileUseCaseRequest(
                userId: $this->resolveUserId($user),
                name: $input['name'],
                email: $input['email'],
                isEmailChanging: $isEmailChanging,
                mustVerifyEmail: true,
            )
        );

        $user->refresh();

        if ($result->shouldSendEmailVerificationNotification) {
            $user->sendEmailVerificationNotification();
        }
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
