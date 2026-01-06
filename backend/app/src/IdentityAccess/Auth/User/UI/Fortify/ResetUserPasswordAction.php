<?php

declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Fortify;

use App\Src\IdentityAccess\Auth\User\Application\Request\ResetUserPasswordUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\ResetUserPasswordUseCase;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPasswordAction implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private readonly ResetUserPasswordUseCase $resetUserPasswordUseCase
    ) {}

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

        $this->resetUserPasswordUseCase->execute(new ResetUserPasswordUseCaseRequest(
            userId: $this->resolveUserId($user),
            password: $input['password'],
        ));
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
