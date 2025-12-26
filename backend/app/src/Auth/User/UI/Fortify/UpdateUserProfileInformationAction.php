<?php

namespace App\Src\Auth\User\UI\Fortify;

use App\Src\Auth\User\Application\Request\UpdateUserProfileUseCaseRequest;
use App\Src\Auth\User\Application\UseCase\UpdateUserProfileUseCase;
use App\Src\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformationAction implements UpdatesUserProfileInformation
{
    public function __construct(
        private readonly UpdateUserProfileUseCase $useCase
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

        $result = $this->useCase->execute(new UpdateUserProfileUseCaseRequest(
            userId: (int) $user->getAuthIdentifier(),
            name: $input['name'],
            email: $input['email'],
            isEmailChanging: $isEmailChanging,
            mustVerifyEmail: $user instanceof MustVerifyEmail,
        ));

        $user->refresh();

        if ($result->shouldSendEmailVerificationNotification && $user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }
    }
}
