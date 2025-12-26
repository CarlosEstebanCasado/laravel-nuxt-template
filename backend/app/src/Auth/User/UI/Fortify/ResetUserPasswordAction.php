<?php

namespace App\BoundedContext\Auth\User\UI\Fortify;

use App\BoundedContext\Auth\User\Application\Request\ResetUserPasswordUseCaseRequest;
use App\BoundedContext\Auth\User\Application\UseCase\ResetUserPasswordUseCase;
use App\BoundedContext\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPasswordAction implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private readonly ResetUserPasswordUseCase $useCase
    ) {
    }

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $this->useCase->execute(new ResetUserPasswordUseCaseRequest(
            userId: (int) $user->getAuthIdentifier(),
            password: $input['password'],
        ));
    }
}
